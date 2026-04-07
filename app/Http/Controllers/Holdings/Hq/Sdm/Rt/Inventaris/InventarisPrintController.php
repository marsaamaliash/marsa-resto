<?php

namespace App\Http\Controllers\Holdings\Hq\Sdm\Rt\Inventaris;

use App\Http\Controllers\Controller;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;
use Illuminate\Http\Request;

class InventarisPrintController extends Controller
{
    public function print(Request $request)
    {
        try {
            $idsRaw = (string) $request->get('ids', '');
            if (trim($idsRaw) === '') {
                return 'Tidak ada ID yang dikirim.';
            }

            $rawKodes = array_values(array_filter(array_map('trim', explode(',', $idsRaw))));

            // optional: buang duplikat
            $rawKodes = array_values(array_unique($rawKodes));

            // optional: regex aman (kode label kamu pakai titik)
            $rawKodes = array_values(array_filter($rawKodes, function ($v) {
                return preg_match('/^[A-Za-z0-9]+(\.[A-Za-z0-9]+)+$/', $v);
            }));

            if (empty($rawKodes)) {
                return 'Format ID tidak valid.';
            }

            $items = Inventaris::query()
                ->whereIn('kode_label', $rawKodes)
                ->whereNull('deleted_at') // redundant tapi jelas
                ->where('lifecycle_status', 'active') // kalau kamu enforce produksi
                ->get()
                ->keyBy('kode_label');

            if ($items->isEmpty()) {
                return 'Data tidak ditemukan di database.';
            }

            // jaga urutan sesuai pilihan user
            $ordered = collect($rawKodes)->map(fn ($k) => $items->get($k))->filter()->values();

            return view('livewire.holdings.hq.sdm.rt.inventaris.inventaris-print-bulk', [
                'items' => $ordered,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}
