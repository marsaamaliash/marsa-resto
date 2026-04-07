<?php

namespace App\Actions\Sso;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetAuthUserPasswordAction
{
    public function execute(AuthApproval $approval, AuthUser $approver): void
    {
        abort_unless($approval->module_code === '00000', 422, 'Invalid module_code.');
        abort_unless($approval->permission_code === 'SSO_USER_PASSWORD_RESET', 422, 'Invalid permission_code.');

        // ✅ enforce approver role DEV
        $isDev = DB::table('auth_user_roles as ur')
            ->join('auth_roles as r', 'r.id', '=', 'ur.role_id')
            ->where('ur.auth_user_id', (int) $approver->id)
            ->where('r.code', 'DEV')
            ->exists();

        if (! $isDev) {
            abort(403, 'Hanya DEV yang boleh approve reset password super admin.');
        }

        $payload = (array) ($approval->action_payload ?? []);
        $targetUserId = (int) ($payload['target_user_id'] ?? 0);
        abort_unless($targetUserId > 0, 422, 'Payload invalid: target_user_id missing.');

        DB::transaction(function () use ($approval, $targetUserId, $approver) {

            // lock approval to avoid race
            $locked = AuthApproval::where('id', $approval->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                abort(409, 'Approval sudah diproses.');
            }

            // lock target user
            $target = AuthUser::query()->lockForUpdate()->findOrFail($targetUserId);

            // safety: must be super admin
            if (! $target->isSuperAdmin()) {
                abort(422, 'Target bukan super admin.');
            }

            $defaultPlain = 'password123';

            DB::table('auth_users')->where('id', $targetUserId)->update([
                'password' => Hash::make($defaultPlain),
                'must_change_password' => 1,
                'password_changed_at' => null,
                'remember_token' => null,
                'updated_at' => now(),
            ]);

            // revoke tokens sanctum
            DB::table('auth_personal_access_tokens')
                ->where('tokenable_type', AuthUser::class)
                ->where('tokenable_id', $targetUserId)
                ->delete();

            // mark approval approved
            $locked->update([
                'status' => 'approved',
                'approved_by' => (int) $approver->id,
                'approved_at' => now(),
            ]);

            // audit
            DB::table('auth_audit_logs')->insert([
                'user_id' => (int) $approver->id,
                'module_code' => '00000',
                'action' => 'SSO_USER_PASSWORD_RESET_APPROVED',
                'payload' => json_encode([
                    'approval_id' => (int) $locked->id,
                    'target_user_id' => (int) $targetUserId,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // clear cache target (kalau kamu pakai cache permission/module)
            $target->clearAuthCache();
        });
    }
}
