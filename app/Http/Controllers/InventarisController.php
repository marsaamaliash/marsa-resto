<?php

namespace App\Http\Controllers;

use App\Models\Inv_Holding;
use App\Models\Inv_Jenis_Barang;
use App\Models\Inv_Lokasi;
use App\Models\Inv_Ruangan;
use App\Models\Inventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Milon\Barcode\DNS1D;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data dropdown
        $data_holding = Inv_Holding::all();
        $data_lokasi = Inv_Lokasi::all();
        $data_ruangan = Inv_Ruangan::all();
        $data_jenis_barang = Inv_Jenis_Barang::all();

        // $data_no_akhir = Inventaris::where('kode_label', "no_urut")->get();

        // Ambil data inventaris untuk tab "Print"
        $data_inventaris = Inventaris::with('holdingNya', 'lokasiNya', 'ruanganNya', 'jenis_barangNya')
            ->when($request->search, function ($query, $search) {
                $query->where('kode_label', 'like', "%$search%")
                    ->orWhere('nama_barang', 'like', "%$search%");
            })
            ->orderBy('nama_barang', 'desc')
            ->paginate(10);

        return view('inventaris.index', compact('data_holding', 'data_lokasi', 'data_ruangan', 'data_jenis_barang', 'data_inventaris'));
    }

    public function store(Request $request)
    {
        $holding = Inv_Holding::findOrFail($request->holding_kode);
        $lokasi = Inv_Lokasi::findOrFail($request->lokasi_kode);
        $ruangan = Inv_Ruangan::findOrFail($request->ruangan_kode);
        $jenis_barang = Inv_Jenis_Barang::findOrFail($request->jenis_barang_kode);

        $last = Inventaris::where('ab', $request->holding_kode)
            ->where('cd', $request->lokasi_kode)
            ->where('ef', $request->ruangan_kode)
            ->where('gh', $request->jenis_barang_kode)
            ->max('no_urut');

        $nextNoUrut = $last ? $last + 1 : 1;
        $qty = (int) $request->qty;

        $createdLabels = []; // simpan semua kode_label

        for ($i = 0; $i < $qty; $i++) {
            $urut = $nextNoUrut + $i;
            $ijk = str_pad($urut, 3, '0', STR_PAD_LEFT);

            // 🔹 Bulan
            $bulanValue = $request->filled('bulan') ? (int) $request->bulan : null;
            $bulanLabel = $bulanValue ? str_pad($bulanValue, 2, '0', STR_PAD_LEFT) : '00';

            // 🔹 Tahun (kalau kosong → "00")
            $tahunValue = $request->filled('tahun') ? (int) $request->tahun : null;
            $tahunLabel = $tahunValue ? str_pad($tahunValue, 2, '0', STR_PAD_LEFT) : '00';

            // 🔹 Bentuk kode label
            $kodeLabel = "{$request->holding_kode}.{$request->lokasi_kode}{$request->ruangan_kode}{$request->jenis_barang_kode}{$ijk}.{$bulanLabel}{$tahunLabel}";

            Inventaris::create([
                'kode_label' => $kodeLabel,
                'nama_barang' => $request->nama_barang,
                'description' => $request->description,
                'ab' => $request->holding_kode,
                'cd' => $request->lokasi_kode,
                'ef' => $request->ruangan_kode,
                'gh' => $request->jenis_barang_kode,
                'ijk' => $ijk,
                'no_urut' => $urut,
                'bulan' => $bulanValue,   // null kalau kosong
                'tahun' => $tahunValue,   // null kalau kosong
            ]);

            $createdLabels[] = $kodeLabel;
        }

        // Simpan di session buat SweetAlert
        session()->flash('success', true);
        session()->flash('createdLabels', $createdLabels);
        session()->flash('quantity', $qty);

        return redirect()->route('inventaris.index', ['tab' => 'create']);
    }

    public function show($kode)
    {
        $inventaris = $this->findOrFail($kode);

        return view('inventaris.show', compact('inventaris'));
    }

    public function edit($kode)
    {
        $inventaris = $this->findOrFail($kode);

        // dd($inventaris);
        return view('inventaris.edit', compact('inventaris'));
    }

    public function update(Request $request, $kode)
    {
        // cari record berdasarkan kode_label
        $dataList = Inventaris::findOrFail($kode);

        // validasi field utama
        $data = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:Baik,Rusak,Hilang,Dalam Perbaikan',
            'tanggal_status' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            $dataList->update($data);

            DB::commit();

            return redirect()->route('inventaris.index')
                ->with('edited', "Inventaris {$dataList->kode_label} berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy($kode)
    {
        $inventaris = Inventaris::where('kode_label', $kode)->firstOrFail();
        $inventaris->delete();

        //     findOrFail($kode);
        // $kk = Kk::where('wilayah_id', $this->wilayah->id)
        //             ->where('kartu_keluarga_id', $kartu_keluarga_id)
        //             ->firstOrFail();

        return redirect()->route('inventaris.index')->with('success', 'Data berhasil dihapus.');
    }

    public function print($kode)
    {
        $item = Inventaris::with(['holding', 'lokasi', 'ruangan', 'jenis_barang'])->findOrFail($kode);

        // QR Code bisa langsung dibuat di blade dengan {!! QrCode::size(150)->generate($item->kode_label) !!}
        return view('inventaris.print', compact('item'));
    }

    // QR Code sudah beres
    public function qrcode($kode)
    {
        return response(QrCode::size(150)->generate($kode))
            ->header('Content-Type', 'image/svg+xml');
    }

    public function printBulk(Request $request)
    {
        $selected = $request->input('selected', []);

        if (empty($selected)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        // Ambil data inventaris berdasarkan kode_label
        $items = \App\Models\Inventaris::whereIn('kode_label', $selected)->get();

        return view('inventaris.print-bulk', compact('items'));
    }

    private function findOrFail($kode)
    {
        return Inventaris::where('kode_label', $kode)
            ->firstOrFail();
    }

    private function validateFields(Request $request)
    {
        return $request->validate([
            'nama_barang' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'foto' => 'nullable|string|max:255',
            'status' => 'required|string',
            'tanggal_status' => 'nullable|date',
        ]);
    }
}
