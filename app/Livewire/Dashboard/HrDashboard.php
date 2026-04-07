<?php

namespace App\Livewire\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class HrDashboard extends Component
{
    public array $breadcrumbs = [];

    public array $tiles = [];

    /** Module code untuk HR */
    private string $moduleCode = '01001';

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->tiles = $this->buildTiles();
    }

    /**
     * BEST PRACTICE:
     * Dashboard tile menggunakan sumber yang sama dengan sidebar: auth_nav_items.
     * Gate: module -> permission -> route exists.
     */
    protected function buildTiles(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        // Gate paling depan: module
        if (! $user->hasModule($this->moduleCode)) {
            return [];
        }

        // Cari root nav item untuk module (mis: nav_code = '01001')
        $root = DB::table('auth_nav_items')
            ->where('module_code', $this->moduleCode)
            ->whereNull('parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->first(['id', 'nav_code']);

        $q = DB::table('auth_nav_items')
            ->where('module_code', $this->moduleCode)
            ->where('is_active', 1);

        // Prioritas: children dari root (paling bersih)
        if ($root) {
            $q->where('parent_id', (int) $root->id);
        } else {
            // fallback kalau root belum ada
            $q->where('nav_code', 'like', $this->moduleCode.'.%');
        }

        $items = $q->orderBy('sort_order')->get([
            'nav_code',
            'label',
            'route_name',
            'permission_code',
            'icon',
        ]);

        // Mapping gambar tile (UI only). Akses tetap dari auth_nav_items.
        $imgMap = [
            '01001.EMPLOYEES' => 'images/tb-sdm-hr-employee.PNG',
            // kalau nanti kamu daftarkan:
            // '01001.RECRUITMENT' => 'images/tb-sdm-hr-recruitment.png',
            // '01001.ATTENDANCE'  => 'images/tb-sdm-hr-attendance.png',
            // '01001.OUTSTANDING' => 'images/tb-sdm-hr-outstanding.png',
        ];

        $out = [];

        foreach ($items as $it) {
            $perm = trim((string) ($it->permission_code ?? ''));
            $routeName = trim((string) ($it->route_name ?? ''));

            // Gate permission (kalau null = module-only)
            if ($perm !== '' && ! $user->hasPermission($perm)) {
                continue;
            }

            // Route harus valid
            if ($routeName === '' || ! Route::has($routeName)) {
                continue;
            }

            $navCode = (string) ($it->nav_code ?? '');

            $out[] = [
                'nav_code' => $navCode,
                'label' => (string) ($it->label ?? ''),
                'route' => $routeName,
                'icon' => (string) ($it->icon ?? ''),
                'img' => $imgMap[$navCode] ?? null,
            ];
        }

        return $out;
    }

    public function render()
    {
        return view('livewire.dashboard.hr-dashboard', [
            'breadcrumbs' => $this->breadcrumbs,
            'tiles' => $this->tiles,
        ])->layout('components.sccr-layout');
    }
}
