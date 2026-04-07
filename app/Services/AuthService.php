<?php

namespace App\Services;

use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly AuthAuditService $audit
    ) {}

    private function isDefaultPasswordPlain(string $plain): bool
    {
        // policy: mengandung password123 dalam bentuk apapun
        return stripos($plain, 'password123') !== false;
    }

    private function isDefaultPasswordHash(string $hashed): bool
    {
        // hash check hanya bisa exact candidate, bukan "mengandung"
        return Hash::check('password123', $hashed)
            || Hash::check('Password123', $hashed)
            || Hash::check('PASSWORD123', $hashed);
    }

    public function login(string $login, string $password, bool $remember = false): void
    {
        $login = trim($login);

        // (1) VALIDASI RAW DULU
        $typedDefault = $this->isDefaultPasswordPlain((string) $password);

        /** @var AuthUser|null $user */
        $user = AuthUser::with('identity')
            ->where(function ($q) use ($login) {
                $q->where('email', $login)
                    ->orWhere('username', $login);
            })
            ->first();

        if (! $user) {
            // tidak bocorkan apakah user ada/tidak
            $this->audit->log('AUTH_LOGIN_FAIL', null, ['channel' => 'web']);
            throw new \RuntimeException('Login gagal. Periksa NIP/Email dan password.');
        }

        if ((int) $user->is_locked === 1) {
            $this->audit->log('AUTH_LOGIN_FAIL', $user, ['channel' => 'web', 'reason' => 'locked']);
            throw new \RuntimeException('Akun dikunci. Hubungi administrator.');
        }

        if (! $user->identity || (int) $user->identity->is_active !== 1) {
            $this->audit->log('AUTH_LOGIN_FAIL', $user, ['channel' => 'web', 'reason' => 'identity_inactive']);
            throw new \RuntimeException('Akun Anda tidak aktif. Hubungi administrator.');
        }

        // (2) HASH check SETELAH validasi raw
        $storedDefault = $this->isDefaultPasswordHash((string) $user->password);

        // (3) AUTH ATTEMPT
        $ok = Auth::attempt(['username' => $user->username, 'password' => $password], $remember);

        if (! $ok) {
            $this->audit->log('AUTH_LOGIN_FAIL', $user, ['channel' => 'web', 'reason' => 'invalid_credential']);
            throw new \RuntimeException('Login gagal. Periksa NIP/Email dan password.');
        }

        session()->regenerate();

        /** @var AuthUser|null $authed */
        $authed = auth()->user();
        if (! $authed) {
            Auth::logout();
            $this->audit->log('AUTH_LOGIN_FAIL', $user, ['channel' => 'web', 'reason' => 'session_error']);
            throw new \RuntimeException('Login gagal. Silakan coba lagi.');
        }

        // must_change_password:
        // - HANYA set ke 1 jika typedDefault atau storedDefault
        // - JANGAN auto-reset ke 0 saat login (reset di proses change password saja)
        $must = (int) ($authed->must_change_password ?? 0);
        if ($typedDefault || $storedDefault) {
            $must = 1;
        }

        DB::connection('mysql')->table('auth_users')->where('id', (int) $authed->id)->update([
            'last_login_at' => now(),
            'must_change_password' => $must,
            'updated_at' => now(),
        ]);

        $this->audit->log('AUTH_LOGIN_OK', $authed, [
            'channel' => 'web',
        ]);

        $authed->refresh();
        if (method_exists($authed, 'clearAuthCache')) {
            $authed->clearAuthCache();
        }
    }

    public function logout(): void
    {
        if ($u = auth()->user()) {
            $this->audit->log('AUTH_LOGOUT', $u, ['channel' => 'web']);
            if (method_exists($u, 'clearAuthCache')) {
                $u->clearAuthCache();
            }
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
