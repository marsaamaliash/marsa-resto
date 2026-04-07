<?php

namespace App\Actions\Auth;

use App\Models\Auth\AuthApproval;
use App\Models\Auth\AuthUser;
use Illuminate\Support\Facades\DB;

class RequestResetPasswordApprovalAction
{
    public function execute(int $targetUserId, string $reason, int $requesterUserId): AuthApproval
    {
        $user = auth()->user();

        // double-check requester
        if (! $user || (int) $user->id !== (int) $requesterUserId) {
            abort(403, 'Unauthorized requester.');
        }

        // permission check (request)
        $canRequest = $user->isSuperAdmin() || $user->hasPermission('SSO_USER_PASSWORD_RESET');
        if (! $canRequest) {
            abort(403, 'No permission: SSO_USER_PASSWORD_RESET');
        }

        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            abort(422, 'Reason is required and max 255 chars.');
        }

        return DB::transaction(function () use ($targetUserId, $reason, $requesterUserId) {

            // lock target
            $target = AuthUser::query()
                ->lockForUpdate()
                ->findOrFail($targetUserId);

            // ✅ ONLY super admin via approval
            if (! $target->isSuperAdmin()) {
                abort(422, 'Target bukan super admin. Gunakan reset langsung (Mode B).');
            }

            // resolve DEV role id
            $devRoleId = (int) DB::table('auth_roles')->where('code', 'DEV')->value('id');
            if ($devRoleId <= 0) {
                abort(500, 'Role DEV tidak ditemukan.');
            }

            // prevent duplicate pending for same target
            $existsPending = AuthApproval::where('module_code', '00000')
                ->where('permission_code', 'SSO_USER_PASSWORD_RESET')
                ->where('status', 'pending')
                ->whereRaw("JSON_EXTRACT(action_payload, '$.target_user_id') = ?", [$targetUserId])
                ->lockForUpdate()
                ->exists();

            if ($existsPending) {
                abort(409, 'Sudah ada approval pending untuk reset password user ini.');
            }

            return AuthApproval::create([
                'auth_user_id' => $requesterUserId,
                'approver_role_id' => $devRoleId, // ✅ hanya DEV yang boleh approve
                'module_code' => '00000',
                'permission_code' => 'SSO_USER_PASSWORD_RESET',
                'action_payload' => [
                    'target_user_id' => $targetUserId,
                    'reason' => $reason,
                    'requested_at' => now()->toDateTimeString(),
                ],
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'rejected_reason' => null,
            ]);
        });
    }
}
