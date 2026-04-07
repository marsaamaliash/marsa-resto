<?php

namespace App\Actions\Inventaris\Master;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InvMasterLokasiDeleteRequest;
use Illuminate\Support\Facades\DB;

class RequestDeleteInvMasterLokasiAction
{
    public function execute(string $holdingKode, string $lokasiKode, string $reason, int $requesterUserId): InvMasterLokasiDeleteRequest
    {
        $user = auth()->user();

        // double-check auth
        if (! $user || (int) $user->id !== (int) $requesterUserId) {
            abort(403, 'Unauthorized requester.');
        }

        // permission
        if (! $user->hasPermission('INV_MASTER_LOKASI_DELETE')) {
            abort(403, 'No permission: INV_MASTER_LOKASI_DELETE');
        }

        $holdingKode = strtoupper(trim($holdingKode));
        $lokasiKode = strtoupper(trim($lokasiKode));

        if (strlen($holdingKode) !== 2 || strlen($lokasiKode) !== 2) {
            abort(422, 'Invalid key. holding_kode & lokasi_kode must be 2 chars.');
        }

        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            abort(422, 'Reason is required and max 255 chars.');
        }

        return DB::transaction(function () use ($holdingKode, $lokasiKode, $reason, $requesterUserId) {

            // lock master row (inv_lokasi)
            $row = DB::table('inv_lokasi')
                ->where('holding_kode', $holdingKode)
                ->where('kode', $lokasiKode)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                abort(404, "Master lokasi {$holdingKode}.{$lokasiKode} tidak ditemukan.");
            }

            // guard soft-deleted kalau ada deleted_at
            if (property_exists($row, 'deleted_at') && $row->deleted_at !== null) {
                abort(409, 'Lokasi sudah dihapus (deleted_at sudah terisi).');
            }

            // prevent duplicate pending request (lock request row(s))
            $existsPending = InvMasterLokasiDeleteRequest::query()
                ->where('holding_kode', $holdingKode)
                ->where('lokasi_kode', $lokasiKode)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->exists();

            if ($existsPending) {
                abort(409, 'Sudah ada request delete pending untuk lokasi ini.');
            }

            // create request row
            return InvMasterLokasiDeleteRequest::create([
                'holding_kode' => $holdingKode,
                'lokasi_kode' => $lokasiKode,
                'reason' => $reason,
                'requested_by' => $requesterUserId,
                'requested_at' => now(),
                'status' => 'pending',
            ]);
        });
    }
}
