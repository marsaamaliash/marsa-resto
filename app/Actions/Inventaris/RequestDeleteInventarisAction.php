<?php

namespace App\Actions\Inventaris;

use App\Models\Auth\AuthApproval;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;
use Illuminate\Support\Facades\DB;

class RequestDeleteInventarisAction
{
    public function execute(string $kodeLabel, string $reason, int $requesterUserId): AuthApproval
    {
        $user = auth()->user();

        // double-check auth & permission
        if (! $user || (int) $user->id !== (int) $requesterUserId) {
            abort(403, 'Unauthorized requester.');
        }

        if (! $user->hasPermission('INV_DELETE')) {
            abort(403, 'No permission: INV_DELETE');
        }

        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            abort(422, 'Reason is required and max 255 chars.');
        }

        return DB::transaction(function () use ($kodeLabel, $reason, $requesterUserId) {

            // lock inventaris row
            $inv = Inventaris::where('kode_label', $kodeLabel)
                ->lockForUpdate()
                ->firstOrFail();

            // guard state
            if ($inv->deleted_at !== null) {
                abort(409, 'Item sudah di-soft-delete.');
            }

            if ($inv->lifecycle_status === 'pending_delete') {
                abort(409, 'Item sudah dalam proses approval delete.');
            }

            if ($inv->lifecycle_status !== 'active') {
                abort(409, 'Item tidak dalam status active.');
            }

            // prevent duplicate pending approval for same kode_label
            $existsPending = AuthApproval::where('module_code', '01005')
                ->where('permission_code', 'INV_DELETE')
                ->where('status', 'pending')
                ->whereRaw("JSON_EXTRACT(action_payload, '$.kode_label') = ?", [$kodeLabel])
                ->lockForUpdate()
                ->exists();

            if ($existsPending) {
                abort(409, 'Sudah ada approval pending untuk item ini.');
            }

            // create approval
            $approval = AuthApproval::create([
                'auth_user_id' => $requesterUserId,
                'approver_role_id' => null, // single-step: any role w/ approve permission
                'module_code' => '01005',
                'permission_code' => 'INV_DELETE',
                'action_payload' => [
                    'kode_label' => $kodeLabel,
                    'reason' => $reason,
                    'requested_at' => now()->toDateTimeString(),
                ],
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'rejected_reason' => null,
            ]);

            // mark inventaris as pending_delete (no soft delete yet)
            $inv->update([
                'lifecycle_status' => 'pending_delete',
                'updated_by' => $requesterUserId,
                // optional: simpan reason sementara agar terlihat di UI item
                'deleted_reason' => $reason,
            ]);

            return $approval;
        });
    }
}
