<?php

namespace App\Actions\Sso;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthIdentity;
use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class ApproveDeactivateAuthUserAction
{
    public function execute(AuthApproval $approval, int $approverUserId): void
    {
        $approver = auth()->user();
        if (! $approver || (int) $approver->id !== (int) $approverUserId) {
            abort(403, 'Unauthorized approver.');
        }

        // Minimal gate: harus bisa approve inbox
        if (! $approver->hasPermission('APPROVAL_APPROVE')) {
            abort(403, 'No permission: APPROVAL_APPROVE');
        }

        DB::transaction(function () use ($approval, $approverUserId, $approver) {
            $ap = AuthApproval::where('id', $approval->id)->lockForUpdate()->firstOrFail();

            if ($ap->status !== 'pending') {
                abort(409, 'Approval sudah diproses.');
            }
            if ($ap->module_code !== '00000' || $ap->permission_code !== 'SSO_USER_DEACTIVATE') {
                abort(422, 'Approval bukan SSO_USER_DEACTIVATE.');
            }

            $payload = (array) ($ap->action_payload ?? []);
            $targetUserId = (int) ($payload['target_user_id'] ?? 0);
            abort_unless($targetUserId > 0, 422, 'Payload invalid: target_user_id missing.');

            $target = AuthUser::where('id', $targetUserId)->lockForUpdate()->firstOrFail();

            // safety
            if ($target->isSuperAdmin() && ! $approver->isSuperAdmin()) {
                abort(403, 'Tidak boleh menonaktifkan super admin.');
            }

            $identity = AuthIdentity::where('id', $target->identity_id)->lockForUpdate()->firstOrFail();

            // mark approval approved
            $ap->update([
                'status' => 'approved',
                'approved_by' => $approverUserId,
                'approved_at' => now(),
            ]);

            // deactivate identity + lock user
            $identity->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);

            $target->update([
                'is_locked' => 1,
                'updated_at' => now(),
            ]);

            $target->clearAuthCache();
        });
    }
}
