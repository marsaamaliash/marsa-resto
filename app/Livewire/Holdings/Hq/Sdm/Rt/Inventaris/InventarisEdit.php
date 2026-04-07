<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inventaris;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InventarisList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class InventarisEdit extends Component
{
    use WithFileUploads;

    /** =====================================================
     *  INPUT PARAM (route / overlay)
     *  - route param: {kode_label}  -> mount($kode_label)
     *  - overlay embed: :kodeLabel  -> mount($kodeLabel)
     ===================================================== */
    public ?string $kodeLabel = null;

    /** =====================================================
     *  DATA (edit state)
     ===================================================== */
    public string $kode_label = '';

    public string $kode_label_original = '';

    // read-only structure
    public string $ab = '';

    public string $cd = '';

    public string $ef = '';

    public string $gh = '';

    public string $ijk = '';

    // editable label part (string 2 digit for UI)
    public string $Bulan = '00';

    public string $Tahun = '00';

    public string $tahun_display = '0000';

    // fields
    public string $nama_barang = '';

    public ?string $description = null;

    public string $status = 'Baik';

    public ?string $tanggal_status = null;

    // names from view
    public string $holding_nama = '';

    public string $lokasi_nama = '';

    public string $ruangan_nama = '';

    public string $jenis_nama = '';

    // uploads
    public $foto = null;

    public $dokumen = null;

    public ?string $foto_existing = null;

    public ?string $dokumen_existing = null;

    // ui confirm cancel
    public bool $showCancelConfirm = false;

    /** =====================================================
     *  MOUNT
     ===================================================== */
    public function mount(?string $kode_label = null, ?string $kodeLabel = null): void
    {
        $this->kodeLabel = $kodeLabel ?? $kode_label;
        abort_unless($this->kodeLabel, 404, 'Kode label tidak ditemukan');

        $inv = InventarisList::where('kode_label', $this->kodeLabel)->firstOrFail();

        $this->kode_label_original = (string) $inv->kode_label;
        $this->kode_label = (string) $inv->kode_label;

        $this->ab = (string) $inv->ab;
        $this->cd = (string) $inv->cd;
        $this->ef = (string) $inv->ef;
        $this->gh = (string) $inv->gh;
        $this->ijk = (string) $inv->ijk;

        // Bulan/Tahun pada view adalah int -> ubah ke 2 digit string untuk UI
        $bulan = (int) ($inv->Bulan ?? 0);
        $tahun = (int) ($inv->Tahun ?? 0);

        $this->Bulan = str_pad((string) $bulan, 2, '0', STR_PAD_LEFT);
        $this->Tahun = str_pad((string) $tahun, 2, '0', STR_PAD_LEFT);

        $this->nama_barang = (string) ($inv->nama_barang ?? '');
        $this->description = $inv->description;
        $this->status = (string) ($inv->status ?? 'Baik');
        $this->tanggal_status = $inv->tanggal_status;

        $this->foto_existing = $inv->foto ?: null;
        $this->dokumen_existing = $inv->dokumen ?: null;

        $this->holding_nama = (string) ($inv->nama_holding ?? '');
        $this->lokasi_nama = (string) ($inv->nama_lokasi ?? '');
        $this->ruangan_nama = (string) ($inv->nama_ruangan ?? '');
        $this->jenis_nama = (string) ($inv->nama_jenis ?? '');

        $this->syncDerivedLabel();
    }

    /** =====================================================
     *  DERIVED: tahun_display & kode_label preview
     ===================================================== */
    private function syncDerivedLabel(): void
    {
        $lm = str_pad((string) $this->Bulan, 2, '0', STR_PAD_LEFT);
        $no = str_pad((string) $this->Tahun, 2, '0', STR_PAD_LEFT);

        $this->tahun_display = ($no === '00') ? '0000' : ('20'.$no);

        $this->kode_label = "{$this->ab}.{$this->cd}{$this->ef}{$this->gh}{$this->ijk}.{$lm}{$no}";
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['Bulan', 'Tahun'], true)) {
            $this->Bulan = str_pad((string) $this->Bulan, 2, '0', STR_PAD_LEFT);
            $this->Tahun = str_pad((string) $this->Tahun, 2, '0', STR_PAD_LEFT);
            $this->syncDerivedLabel();
        }
    }

    /** =====================================================
     *  VALIDATION
     ===================================================== */
    protected function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:150'],
            'status' => ['required', 'string'],
            'Bulan' => ['required', 'string', 'size:2'],
            'Tahun' => ['required', 'string', 'size:2'],
            'foto' => ['nullable', 'image', 'max:2048'],     // png/jpg/jpeg
            'dokumen' => ['nullable', 'mimes:pdf', 'max:5120'],
            'tanggal_status' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ];
    }

    /** =====================================================
     *  ACTIONS
     ===================================================== */
    public function confirmCancel(): void
    {
        $this->showCancelConfirm = true;
    }

    public function cancel(): void
    {
        $this->showCancelConfirm = false;
        $this->dispatch('inventaris-overlay-close');
    }

    private function bulanInt(): int
    {
        return ($this->Bulan === '00') ? 0 : (int) $this->Bulan;
    }

    private function tahunInt(): int
    {
        return ($this->Tahun === '00') ? 0 : (int) $this->Tahun;
    }

    private function ensureDir(string $path): void
    {
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
    }

    private function fileExt(?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        return $ext ? strtolower($ext) : null;
    }

    public function update(): mixed
    {
        $this->validate();
        $this->syncDerivedLabel();

        $newKode = $this->kode_label;
        $oldKode = $this->kode_label_original;

        // Jika kode berubah, pastikan tidak bentrok
        if ($newKode !== $oldKode && Inventaris::where('kode_label', $newKode)->exists()) {
            $this->addError('Tahun', 'Kode label baru sudah digunakan. Ubah Bulan/Tahun atau periksa data duplikat.');

            return null;
        }

        $pathFoto = public_path('SDM'.DIRECTORY_SEPARATOR.'inventaris'.DIRECTORY_SEPARATOR.'foto');
        $pathDoc = public_path('SDM'.DIRECTORY_SEPARATOR.'inventaris'.DIRECTORY_SEPARATOR.'dokumen');

        $this->ensureDir($pathFoto);
        $this->ensureDir($pathDoc);

        DB::transaction(function () use ($newKode, $oldKode, $pathFoto, $pathDoc) {

            // 1) Rename existing files if kode changes (pertahankan ext lama)
            if ($newKode !== $oldKode) {

                if ($this->foto_existing) {
                    $ext = $this->fileExt($this->foto_existing) ?: 'png';
                    $old = $pathFoto.DIRECTORY_SEPARATOR.$this->foto_existing;

                    if (File::exists($old)) {
                        $newName = $newKode.'.'.$ext;
                        File::move($old, $pathFoto.DIRECTORY_SEPARATOR.$newName);
                        $this->foto_existing = $newName;
                    } else {
                        // kalau file tidak ada, jangan crash, cukup null-kan agar konsisten
                        // (kadang data DB ada, file fisik sudah hilang)
                        // $this->foto_existing tetap dibiarkan agar user bisa lihat link jika file ada di storage lain.
                    }
                }

                if ($this->dokumen_existing) {
                    $ext = $this->fileExt($this->dokumen_existing) ?: 'pdf';
                    $old = $pathDoc.DIRECTORY_SEPARATOR.$this->dokumen_existing;

                    if (File::exists($old)) {
                        $newName = $newKode.'.'.$ext;
                        File::move($old, $pathDoc.DIRECTORY_SEPARATOR.$newName);
                        $this->dokumen_existing = $newName;
                    }
                }
            }

            // 2) Upload new files (overwrite by kode + ext)
            if ($this->foto) {
                $ext = strtolower($this->foto->getClientOriginalExtension() ?: 'png');
                // normalisasi jpg/jpeg
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }

                $filename = $newKode.'.'.$ext;
                $dest = $pathFoto.DIRECTORY_SEPARATOR.$filename;

                // overwrite
                File::put($dest, file_get_contents($this->foto->getRealPath()));
                $this->foto_existing = $filename;
            }

            if ($this->dokumen) {
                $filename = $newKode.'.pdf';
                $dest = $pathDoc.DIRECTORY_SEPARATOR.$filename;

                File::put($dest, file_get_contents($this->dokumen->getRealPath()));
                $this->dokumen_existing = $filename;
            }

            // 3) Persist to table (NOT view)
            // Bulan/Tahun disimpan sebagai INT sesuai struktur tabel
            Inventaris::where('kode_label', $oldKode)->update([
                'kode_label' => $newKode,
                'Bulan' => $this->bulanInt(),
                'Tahun' => $this->tahunInt(),
                'nama_barang' => $this->nama_barang,
                'description' => $this->description,
                'status' => $this->status,
                'tanggal_status' => $this->tanggal_status,
                'foto' => $this->foto_existing,
                'dokumen' => $this->dokumen_existing,
            ]);

            // update original (agar kalau user tetap di overlay lalu edit lagi, referensinya benar)
            $this->kode_label_original = $newKode;
        });

        // Inform parent: toast + close overlay
        $this->dispatch('inventaris-updated', kodeLabel: $newKode);
        $this->dispatch('inventaris-overlay-close');

        return null;
    }

    public function render()
    {
        // overlay child => jangan pakai layout
        return view('livewire.holdings.hq.sdm.rt.inventaris.inventaris-edit');
    }
}
