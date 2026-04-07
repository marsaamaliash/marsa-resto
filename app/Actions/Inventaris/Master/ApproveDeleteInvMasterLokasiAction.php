<?php

namespace App\Actions\Inventaris\Master;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InvMasterLokasiDeleteRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApproveDeleteInvMasterLokasiAction
{
    public function execute(InvMasterLokasiDeleteRequest $request, int $approverUserId): void
    {
        $user = auth()->user();

        if (! $user || (int) $user->id !== (int) $approverUserId) {
            abort(403, 'Unauthorized approver.');
        }

        $canApprove = $user->hasPermission('INV_MASTER_LOKASI_DELETE_APPROVE');
        if (! $canApprove) {
            abort(403, 'No permission: INV_MASTER_LOKASI_DELETE_APPROVE');
        }

        DB::transaction(function () use ($request, $approverUserId) {

            // lock request row
            $locked = InvMasterLokasiDeleteRequest::where('id', $request->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'pending') {
                abort(409, 'Request sudah diproses.');
            }

            $holdingKode = strtoupper(trim((string) $locked->holding_kode));
            $lokasiKode = strtoupper(trim((string) $locked->lokasi_kode));

            // lock master row
            $q = DB::table('inv_lokasi')
                ->where('holding_kode', $holdingKode)
                ->where('kode', $lokasiKode)
                ->lockForUpdate();

            // kalau row tidak ada → tetap approve request (idempotent) atau fail?
            // ERP biasanya idempotent: dianggap sudah tidak ada.
            if ($q->exists()) {
                if (Schema::hasColumn('inv_lokasi', 'deleted_at')) {
                    $q->update(['deleted_at' => now()]);
                } else {
                    $q->delete();
                }
            }

            $locked->update([
                'status' => 'approved',
                'approved_by' => $approverUserId,
                'approved_at' => now(),
            ]);
        });
    }
}
