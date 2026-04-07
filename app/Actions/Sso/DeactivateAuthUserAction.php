<?php

namespace App\Actions\Sso;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthIdentity;
use App\Models\Auth\AuthUser;

class DeactivateAuthUserAction
{
    public function execute(AuthApproval $approval, AuthUser $approver): void
    {
        abort_unless($approval->module_code === '00000', 422, 'Invalid module_code for SSO action.');
        abort_unless($approval->permission_code === 'SSO_USER_DEACTIVATE', 422, 'Invalid permission_code.');

        $payload = (array) ($approval->action_payload ?? []);
        $targetUserId = (int) ($payload['target_user_id'] ?? 0);

        abort_unless($targetUserId > 0, 422, 'Payload invalid: target_user_id missing.');

        $target = AuthUser::query()->lockForUpdate()->findOrFail($targetUserId);

        // safety: jangan deactivate super admin kecuali approver super admin
        if ($target->isSuperAdmin() && ! $approver->isSuperAdmin()) {
            abort(403, 'Tidak boleh menonaktifkan super admin.');
        }

        // identity target
        $identity = $target->identity;
        if (! $identity) {
            // fallback safety
            $identity = AuthIdentity::query()->lockForUpdate()->find($target->identity_id);
        }
        abort_unless($identity, 422, 'Target identity tidak ditemukan.');

        // deactivate
        $identity->update([
            'is_active' => 0,
        ]);

        // clear cache target supaya hak akses tidak nyangkut
        $target->clearAuthCache();
    }
}
