<?php

namespace App\Actions\Inventaris\Master;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InvMasterLokasiDeleteRequest;
use Illuminate\Support\Facades\DB;

class RejectDeleteInvMasterLokasiAction
{
    public function execute(InvMasterLokasiDeleteRequest $request, int $approverUserId, string $rejectReason): void
    {
        $user = auth()->user();

        if (! $user || (int) $user->id !== (int) $approverUserId) {
            abort(403, 'Unauthorized approver.');
        }

        $canApprove = $user->hasPermission('INV_MASTER_LOKASI_DELETE_APPROVE');
        if (! $canApprove) {
            abort(403, 'No permission: INV_MASTER_LOKASI_DELETE_APPROVE');
        }

        $rejectReason = trim($rejectReason);
        if ($rejectReason !== '' && mb_strlen($rejectReason) > 255) {
            abort(422, 'Reject reason max 255 chars.');
        }

        DB::transaction(function () use ($request, $approverUserId, $rejectReason) {

            $locked = InvMasterLokasiDeleteRequest::where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'pending') {
                abort(409, 'Request sudah diproses.');
            }

            $locked->update([
                'status' => 'rejected',
                'rejected_by' => $approverUserId,
                'rejected_at' => now(),
                'reject_reason' => ($rejectReason !== '' ? $rejectReason : null),
            ]);
        });
    }
}
