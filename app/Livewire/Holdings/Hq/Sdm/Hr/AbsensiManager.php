<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AbsensiManager extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canUpload = false;

    public bool $canGenerate = false;

    public bool $canDownload = false;

    public $uploadedFile;

    public array $previewData = [];

    public string $periodeAwal = '';

    public string $periodeAkhir = '';

    public string $activeTab = 'upload';

    public array $generateResult = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Absensi', 'color' => 'text-gray-900 font-semibold'],
        ];

        $user = auth()->user();
        $this->canUpload = (bool) ($user?->hasPermission('ABS_UPLOAD') ?? false);
        $this->canGenerate = (bool) ($user?->hasPermission('ABS_GENERATE') ?? false);
        $this->canDownload = (bool) ($user?->hasPermission('ABS_DOWNLOAD') ?? true);
    }

    public function upload(): void
    {
        if (! $this->canUpload) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin upload absensi.'];

            return;
        }

        $this->validate([
            'uploadedFile' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($this->uploadedFile->getRealPath());

            $sheet = $spreadsheet->getSheetByName('Catatan');
            if (! $sheet) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Sheet "Catatan" tidak ditemukan.'];

                return;
            }

            [$startDate, $endDate, $numDays] = $this->extractPeriode($sheet);
            $startDayCol = $this->findStartDayColumn($sheet);
            $records = $this->parseAbsensiData($sheet, $startDate, $numDays, $startDayCol);

            $this->previewData = $records;
            $this->periodeAwal = $startDate->format('d/m/Y');
            $this->periodeAkhir = $endDate->format('d/m/Y');
            $this->generateResult = [];
            $this->activeTab = 'dashboard';

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'File berhasil diupload dan data berhasil digenerate. Silakan cek di tab Dashboard Data.'];
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Gagal memproses file: '.$e->getMessage()];
        }
    }

    public function generate(): void
    {
        if (! $this->canGenerate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin generate absensi.'];

            return;
        }

        if (empty($this->previewData)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak ada data untuk digenerate.'];

            return;
        }

        $baru = [];
        $sudahAda = [];

        foreach ($this->previewData as $row) {
            $exists = \DB::table('absensi')
                ->where('nama', $row['nama'])
                ->where('tanggal', $row['tanggal'])
                ->exists();

            if ($exists) {
                $sudahAda[] = $row;
            } else {
                \DB::table('absensi')->insert([
                    'no' => $row['no'],
                    'nama' => $row['nama'],
                    'dept' => $row['dept'],
                    'tanggal' => Carbon::createFromFormat('d-m-Y', $row['tanggal'])->format('Y-m-d'),
                    'jam_masuk' => $row['jam_masuk'],
                    'jam_keluar' => $row['jam_keluar'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $baru[] = $row;
            }
        }

        $this->generateResult = [
            'baru' => $baru,
            'sudah_ada' => $sudahAda,
        ];

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data absensi berhasil digenerate ke database.'];
    }

    public function download()
    {
        if (empty($this->previewData)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak ada data untuk diunduh. Silakan upload file terlebih dahulu.'];

            return null;
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Nama', 'Dept', 'Tanggal', 'Jam Masuk', 'Jam Keluar'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.'1', $header);
            $col++;
        }

        $row = 2;
        foreach ($this->previewData as $data) {
            $sheet->setCellValue("A{$row}", $data['no']);
            $sheet->setCellValue("B{$row}", $data['nama']);
            $sheet->setCellValue("C{$row}", $data['dept']);
            $sheet->setCellValue("D{$row}", $data['tanggal']);
            $sheet->setCellValue("E{$row}", $data['jam_masuk']);
            $sheet->setCellValue("F{$row}", $data['jam_keluar']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'absensi_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function extractPeriode($sheet): array
    {
        $periodeCell = trim((string) $sheet->getCell('C2')->getFormattedValue());
        $periodeCell = preg_replace('/\s+/u', ' ', $periodeCell);

        preg_match_all(
            '/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/u',
            $periodeCell,
            $matches,
            PREG_SET_ORDER
        );

        if (count($matches) < 2) {
            throw new \Exception('Format periode tidak dikenali di C2: "'.$periodeCell.'"');
        }

        $startDate = $this->parseDMY($matches[0]);
        $endDate = $this->parseDMY($matches[1]);
        $numDays = $startDate->diffInDays($endDate) + 1;

        return [$startDate, $endDate, $numDays];
    }

    private function parseDMY(array $match): Carbon
    {
        [$full, $d, $m, $y] = $match;
        $y = (int) $y;
        if ($y < 100) {
            $y += ($y >= 70 ? 1900 : 2000);
        }

        return Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', $d, $m, $y));
    }

    private function findStartDayColumn($sheet): int
    {
        $highestColIdx = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        for ($c = 1; $c <= $highestColIdx; $c++) {
            $addr = Coordinate::stringFromColumnIndex($c).'3';
            $val = trim((string) $sheet->getCell($addr)->getFormattedValue());

            if ($val === '1' || $val === '01') {
                return $c;
            }
        }

        return 4;
    }

    private function parseAbsensiData($sheet, Carbon $startDate, int $numDays, int $startDayCol): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColIdx = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        $records = [];

        for ($row = 5; $row <= $highestRow; $row++) {
            $no = trim((string) $sheet->getCell('A'.$row)->getFormattedValue());
            $nama = trim((string) $sheet->getCell('B'.$row)->getFormattedValue());
            $dept = trim((string) $sheet->getCell('C'.$row)->getFormattedValue());

            if ($no === '' && $nama === '' && $dept === '') {
                continue;
            }

            for ($d = 0; $d < $numDays; $d++) {
                $colIdx = $startDayCol + $d;
                if ($colIdx > $highestColIdx) {
                    break;
                }

                $addr = Coordinate::stringFromColumnIndex($colIdx).$row;
                $cellText = preg_replace('/\s+/u', ' ', trim((string) $sheet->getCell($addr)->getFormattedValue()));

                if ($cellText === '') {
                    continue;
                }

                $times = $this->extractTimes($cellText);
                if (empty($times)) {
                    continue;
                }

                $tanggal = $startDate->copy()->addDays($d)->format('d-m-Y');
                $jamMasuk = $times[0];
                $jamKeluar = count($times) > 1 ? end($times) : null;

                $records[] = [
                    'no' => $no,
                    'nama' => $nama,
                    'dept' => $dept,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $jamMasuk,
                    'jam_keluar' => $jamKeluar,
                ];
            }
        }

        return $records;
    }

    private function extractTimes(string $cellText): array
    {
        $tokens = preg_split('/[\s,]+/u', $cellText, -1, PREG_SPLIT_NO_EMPTY);
        $times = [];

        foreach ($tokens as $t) {
            if (preg_match('/^\d{1,2}[:.]\d{2}$/', $t)) {
                $times[] = str_replace('.', ':', $t);
            }
        }

        return $times;
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-manager', [
            'breadcrumbs' => $this->breadcrumbs,
        ])->layout('components.sccr-layout');
    }
}
