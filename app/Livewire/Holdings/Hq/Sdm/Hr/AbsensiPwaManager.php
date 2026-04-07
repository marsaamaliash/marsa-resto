<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\AbsensiPWA;
use App\Models\Holding;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AbsensiPwaManager extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public $id_holding = '';

    public string $jenis = '';

    public $latitude = '';

    public $longitude = '';

    public $holdings = [];

    public $todayLogs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Absensi PWA', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->loadData();
    }

    public function loadData(): void
    {
        $this->holdings = Holding::orderBy('nama_holding')->get();

        $today = Carbon::today();
        $user = auth()->user();
        $karyawan = Karyawan::where('nik', $user->nik)->firstOrFail();

        $this->todayLogs = AbsensiPWA::with('holding')
            ->where('nik', $karyawan->nik)
            ->whereDate('tanggal', $today)
            ->orderBy('jam')
            ->get();
    }

    public function absen(string $jenis, float $latitude, float $longitude): void
    {
        $this->jenis = $jenis;
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        $this->validate([
            'id_holding' => ['required', Rule::exists('holding', 'id_holding')],
            'jenis' => ['required', Rule::in(['In', 'Out'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $user = auth()->user();
            $karyawan = Karyawan::where('nik', $user->nik)->firstOrFail();
            $holding = Holding::findOrFail($this->id_holding);
            $today = Carbon::today();

            $distance = $this->distanceMeters(
                (float) $this->latitude,
                (float) $this->longitude,
                (float) $holding->latitude,
                (float) $holding->longitude
            );

            if ($distance > $holding->radius_meter) {
                $this->toast = [
                    'show' => true,
                    'type' => 'error',
                    'message' => "Anda berada di luar radius absensi ({$holding->radius_meter} m). Jarak terdeteksi: ".round($distance).' m.',
                ];

                return;
            }

            AbsensiPWA::create([
                'nik' => $karyawan->nik,
                'id_holding' => $holding->id_holding,
                'tanggal' => $today->toDateString(),
                'jenis' => $this->jenis,
                'jam' => Carbon::now()->toTimeString(),
            ]);

            $this->toast = [
                'show' => true,
                'type' => 'success',
                'message' => "Absensi {$this->jenis} berhasil. Jarak: ".round($distance).' m.',
            ];

            $this->loadData();
        } catch (\Throwable $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Gagal melakukan absensi: '.$e->getMessage()];
        }
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371000;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlambda = deg2rad($lon2 - $lon1);

        $a = sin($dphi / 2) * sin($dphi / 2) +
             cos($phi1) * cos($phi2) *
             sin($dlambda / 2) * sin($dlambda / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.absensi-pwa-manager', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdings' => $this->holdings,
            'todayLogs' => $this->todayLogs,
        ])->layout('components.sccr-layout');
    }
}
