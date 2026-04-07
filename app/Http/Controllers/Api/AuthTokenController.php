<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthUser;
use App\Services\AuthAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthTokenController extends Controller
{
    public function __construct(
        private readonly AuthAuditService $audit
    ) {}

    private function isDefaultPasswordPlain(string $plain): bool
    {
        return stripos($plain, 'password123') !== false;
    }

    private function isDefaultPasswordHash(string $hashed): bool
    {
        return Hash::check('password123', $hashed)
            || Hash::check('Password123', $hashed)
            || Hash::check('PASSWORD123', $hashed);
    }

    public function issue(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_id' => ['nullable', 'string', 'max:120'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'client_key' => ['nullable', 'string', 'max:80'],
            'client_secret' => ['nullable', 'string', 'max:255'],
        ]);

        // (1) RAW dulu
        $typedDefault = $this->isDefaultPasswordPlain((string) $data['password']);

        $login = trim($data['login']);

        /** @var AuthUser|null $user */
        $user = AuthUser::with('identity')
            ->where(function ($q) use ($login) {
                $q->where('email', $login)->orWhere('username', $login);
            })
            ->first();

        if (! $user) {
            $this->audit->log('AUTH_TOKEN_FAIL', null, ['channel' => 'api']);
            abort(422, 'Login gagal.');
        }

        if ((int) $user->is_locked === 1) {
            $this->audit->log('AUTH_TOKEN_FAIL', $user, ['channel' => 'api', 'reason' => 'locked']);
            abort(403, 'Akun dikunci.');
        }

        if (! $user->identity || (int) $user->identity->is_active !== 1) {
            $this->audit->log('AUTH_TOKEN_FAIL', $user, ['channel' => 'api', 'reason' => 'identity_inactive']);
            abort(403, 'Akun tidak aktif.');
        }

        // (2) HASH check setelah raw
        $storedDefault = $this->isDefaultPasswordHash((string) $user->password);

        if (! Hash::check((string) $data['password'], (string) $user->password)) {
            $this->audit->log('AUTH_TOKEN_FAIL', $user, ['channel' => 'api', 'reason' => 'invalid_credential']);
            abort(422, 'Login gagal.');
        }

        // Optional: validasi client
        $clientId = null;
        if (! empty($data['client_key'])) {
            $row = DB::connection('mysql')->table('auth_api_clients')
                ->where('client_key', $data['client_key'])
                ->first();

            if (! $row || (int) $row->is_active !== 1) {
                abort(403, 'Client tidak aktif.');
            }
            if (! Hash::check((string) ($data['client_secret'] ?? ''), (string) $row->client_secret_hash)) {
                abort(403, 'Client secret invalid.');
            }
            $clientId = (int) $row->id;
        }

        // MUST CHANGE policy (konsisten dengan web)
        $must = (int) ($user->must_change_password ?? 0);
        if ($typedDefault || $storedDefault) {
            $must = 1;
        }

        if ((int) ($user->must_change_password ?? 0) !== $must) {
            DB::connection('mysql')->table('auth_users')->where('id', (int) $user->id)->update([
                'must_change_password' => $must,
                'updated_at' => now(),
            ]);
            $user->must_change_password = $must;
        }

        // Abilities
        // - jika forced karena default (typedDefault atau storedDefault) => boleh change tanpa old
        // - jika forced karena policy lain => tetap butuh old
        $abilities = [];
        if ($must === 1) {
            $abilities = ['me:read', 'password:change'];
            if ($typedDefault || $storedDefault) {
                $abilities[] = 'password:change_no_old';
            }
        } else {
            $abilities = ['me:read', 'modules:read'];
        }

        $deviceName = trim((string) ($data['device_name'] ?? 'api'));
        $expiresAt = now()->addHours(12);

        $token = $user->createToken($deviceName, $abilities, $expiresAt);
        $plainAccess = $token->plainTextToken;
        $patId = (int) $token->accessToken->id;

        // Refresh token (rotating)
        $refreshPlain = Str::random(80);
        $refreshHash = hash('sha256', $refreshPlain);

        DB::connection('mysql')->table('auth_api_refresh_tokens')->insert([
            'auth_user_id' => (int) $user->id,
            'personal_access_token_id' => $patId,
            'token_hash' => $refreshHash,
            'device_id' => $data['device_id'] ?? null,
            'client_id' => $clientId,
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->audit->log('AUTH_TOKEN_OK', $user, ['channel' => 'api']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainAccess,
            'expires_at' => $expiresAt->toISOString(),
            'refresh_token' => $refreshPlain,
            'must_change_password' => ($must === 1),
        ]);
    }

    public function refresh(Request $request)
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $hash = hash('sha256', (string) $data['refresh_token']);

        $row = DB::connection('mysql')->table('auth_api_refresh_tokens')
            ->where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $row) {
            abort(401, 'Refresh token invalid/expired.');
        }

        $user = AuthUser::with('identity')->findOrFail((int) $row->auth_user_id);

        DB::transaction(function () use ($row) {
            DB::connection('mysql')->table('auth_personal_access_tokens')
                ->where('id', (int) $row->personal_access_token_id)->delete();

            DB::connection('mysql')->table('auth_api_refresh_tokens')
                ->where('id', (int) $row->id)
                ->update(['revoked_at' => now(), 'updated_at' => now()]);
        });

        $must = (int) ($user->must_change_password ?? 0) === 1;

        $abilities = $must
            ? ['me:read', 'password:change']
            : ['me:read', 'modules:read'];

        $expiresAt = now()->addHours(12);
        $token = $user->createToken('api', $abilities, $expiresAt);
        $plainAccess = $token->plainTextToken;
        $patId = (int) $token->accessToken->id;

        $refreshPlain = Str::random(80);
        $refreshHash = hash('sha256', $refreshPlain);

        DB::connection('mysql')->table('auth_api_refresh_tokens')->insert([
            'auth_user_id' => (int) $user->id,
            'personal_access_token_id' => $patId,
            'token_hash' => $refreshHash,
            'device_id' => $row->device_id ?? null,
            'client_id' => $row->client_id ?? null,
            'expires_at' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->audit->log('AUTH_TOKEN_REFRESH', $user, ['channel' => 'api']);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $plainAccess,
            'expires_at' => $expiresAt->toISOString(),
            'refresh_token' => $refreshPlain,
            'must_change_password' => $must,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        if ($token) {
            DB::connection('mysql')->table('auth_api_refresh_tokens')
                ->where('personal_access_token_id', (int) $token->id)
                ->update(['revoked_at' => now(), 'updated_at' => now()]);

            $token->delete();
        }

        if ($user) {
            $this->audit->log('AUTH_TOKEN_LOGOUT', $user, ['channel' => 'api']);
        }

        return response()->json(['ok' => true]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => (int) $user->id,
            'username' => (string) $user->username,
            'email' => (string) ($user->email ?? ''),
            'must_change_password' => (int) ($user->must_change_password ?? 0) === 1,
            'identity' => $user->identity,
        ]);
    }

    public function modules(Request $request)
    {
        $user = $request->user();
        if (! $user->tokenCan('modules:read')) {
            abort(403, 'Missing ability: modules:read');
        }

        $codes = $user->modules();
        $rows = DB::connection('mysql')->table('auth_modules')
            ->whereIn('code', $codes)
            ->where('is_active', 1)
            ->orderBy('code')
            ->get(['code', 'name', 'route', 'icon']);

        return response()->json(['modules' => $rows]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        // kalau punya ability no_old, current_password boleh null
        $noOld = $user->tokenCan('password:change_no_old');

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        if (! $noOld) {
            $rules['current_password'] = ['required', 'string'];
        } else {
            $rules['current_password'] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        if (! $noOld) {
            if (! Hash::check((string) $data['current_password'], (string) $user->password)) {
                abort(422, 'Password lama tidak sesuai.');
            }
        }

        // Hard block “password123” dalam bentuk apapun (MENGANDUNG)
        if (stripos((string) $data['password'], 'password123') !== false) {
            abort(422, 'Password baru tidak boleh mengandung "password123".');
        }

        $user->password = Hash::make((string) $data['password']);
        $user->must_change_password = 0;
        $user->password_changed_at = now();
        $user->save();

        // revoke semua token lain (aman untuk ERP)
        DB::connection('mysql')->table('auth_personal_access_tokens')
            ->where('tokenable_type', get_class($user))
            ->where('tokenable_id', $user->id)
            ->delete();

        if (method_exists($user, 'clearAuthCache')) {
            $user->clearAuthCache();
        }
        $this->audit->log('AUTH_PW_CHANGED', $user, ['channel' => 'api']);

        return response()->json(['ok' => true]);
    }
}
