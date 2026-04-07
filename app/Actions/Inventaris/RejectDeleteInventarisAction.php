<?php

namespace App\Actions\Inventaris;

use App\Models\Auth\AuthApproval;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;
use Illuminate\Support\Facades\DB;

class RejectDeleteInventarisAction
{
    public function execute(AuthApproval $approval, int $approverUserId, string $rejectReason): void
    {
        $user = auth()->user();

        if (! $user || (int) $user->id !== (int) $approverUserId) {
            abort(403, 'Unauthorized approver.');
        }

        $canApprove = $user->hasPermission('INV_DELETE_APPROVE') || $user->hasPermission('INV_DELETE');
        if (! $canApprove) {
            abort(403, 'No permission to reject delete.');
        }

        $rejectReason = trim($rejectReason);
        if ($rejectReason === '' || mb_strlen($rejectReason) > 255) {
            abort(422, 'Reject reason required (max 255 chars).');
        }

        DB::transaction(function () use ($approval, $approverUserId, $rejectReason) {

            $lockedApproval = AuthApproval::where('id', $approval->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedApproval->status !== 'pending') {
                abort(409, 'Approval sudah diproses.');
            }

            if ($lockedApproval->module_code !== '01005' || $lockedApproval->permission_code !== 'INV_DELETE') {
                abort(422, 'Approval bukan untuk delete inventaris.');
            }

            $payload = $lockedApproval->action_payload ?? [];
            $kodeLabel = $payload['kode_label'] ?? null;

            if (! $kodeLabel) {
                abort(422, 'Payload invalid: kode_label missing.');
            }

            $inv = Inventaris::where('kode_label', $kodeLabel)
                ->lockForUpdate()
                ->firstOrFail();

            // update approval
            $lockedApproval->update([
                'status' => 'rejected',
                'approved_by' => $approverUserId, // tetap simpan siapa yang memutuskan
                'approved_at' => now(),
                'rejected_reason' => $rejectReason,
            ]);

            // revert lifecycle to active if still pending_delete and not soft deleted
            if ($inv->deleted_at === null && $inv->lifecycle_status === 'pending_delete') {
                $inv->update([
                    'lifecycle_status' => 'active',
                    'updated_by' => $approverUserId,
                    // optional: bersihkan reason sementara
                    'deleted_reason' => null,
                ]);
            }
        });
    }
}
