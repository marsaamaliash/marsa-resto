<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Holding;
use App\Models\Holdings\Hq\Sdm\Hr\Emp_Employee_List;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeTable extends Component
{
    use WithPagination;

    /* ===================== UI GLOBAL ===================== */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false; // nanti dipakai jika kamu aktifkan request delete

    /* ===================== FILTER & SORT ===================== */
    public string $search = '';

    public string $filterHolding = '';  // holding_id

    public string $filterPosition = ''; // position_id

    public string $filterStatus = '';   // PKWT|Karyawan Tetap|RESIGN

    public string $filterJoinDate = ''; // YYYY-MM-DD

    public int $perPage = 10;

    public string $sortField = 'nama';

    public string $sortDirection = 'asc';

    protected array $allowedSortFields = [
        'holding_name',
        'department_name',
        'division_name',
        'nip',
        'nama',
        'position_title',
        'employee_status',
        'tanggal_join',
    ];

    /* ===================== SELECTION ===================== */
    public array $selected = []; // ["NIP", ...]

    public bool $selectAll = false;

    /* ===================== OVERLAY ===================== */
    public ?string $overlayMode = null; // null|'create'|'edit'|'show'

    public ?string $overlayKey = null;  // nip

    /* ===================== QUERY STRING ===================== */
    protected $queryString = [
        'search' => ['except' => ''],
        'filterHolding' => ['except' => ''],
        'filterPosition' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterJoinDate' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'nama'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-800'],
            ['label' => 'HR', 'route' => 'dashboard.hr', 'color' => 'text-gray-800'],
            ['label' => 'Employee', 'color' => 'text-gray-900 font-semibold'],
        ];

        $user = auth()->user();
        $this->canCreate = (bool) ($user?->hasPermission('EMP_CREATE') ?? false);
        $this->canUpdate = (bool) ($user?->hasPermission('EMP_UPDATE') ?? false);
        $this->canDelete = (bool) ($user?->hasPermission('EMP_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    /* ===================== QUERY ===================== */
    private function tableListQuery()
    {
        $requested = (string) $this->sortField;
        $sortField = in_array($requested, $this->allowedSortFields, true) ? $requested : 'nama';
        $sortDir = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Emp_Employee_List::query()
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);
                $q->where(function ($w) use ($s) {
                    $w->where('nama', 'like', "%{$s}%")
                        ->orWhere('nip', 'like', "%{$s}%")
                        ->orWhere('holding_name', 'like', "%{$s}%")
                        ->orWhere('department_name', 'like', "%{$s}%")
                        ->orWhere('division_name', 'like', "%{$s}%")
                        ->orWhere('position_title', 'like', "%{$s}%")
                        ->orWhere('job_title_name', 'like', "%{$s}%");
                });
            })
            ->when($this->filterHolding !== '', fn ($q) => $q->where('holding_id', (int) $this->filterHolding))
            ->when($this->filterPosition !== '', fn ($q) => $q->where('position_id', (int) $this->filterPosition))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('employee_status', $this->filterStatus))
            ->when($this->filterJoinDate !== '', fn ($q) => $q->whereDate('tanggal_join', $this->filterJoinDate))
            ->orderBy($sortField, $sortDir)
            ->orderBy('nip', 'asc'); // stabil
    }

    protected function visibleNips(): array
    {
        $p = $this->tableListQuery()->paginate($this->perPage);

        return $p->getCollection()
            ->pluck('nip')
            ->map(fn ($x) => (string) $x)
            ->toArray();
    }

    /* ===================== SORT ===================== */
    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            $this->resetPage();

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        $this->resetPage();
    }

    /* ===================== FILTER ===================== */
    public function applyFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterHolding', 'filterPosition', 'filterStatus', 'filterJoinDate']);
        $this->applyFilter();
    }

    public function updated($property): void
    {
        if (in_array($property, [
            'search', 'perPage', 'sortField', 'sortDirection',
            'filterHolding', 'filterPosition', 'filterStatus', 'filterJoinDate',
        ], true)) {
            $this->resetPage();
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    /* ===================== SELECTION ===================== */
    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->visibleNips();

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $visible)));

            return;
        }

        $this->selected = array_values(array_diff($this->selected, $visible));
    }

    public function updatedSelected(): void
    {
        $visible = $this->visibleNips();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));
    }

    /* ===================== EXPORT ===================== */
    public function exportFiltered()
    {
        $data = $this->tableListQuery()->get();

        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih karyawan terlebih dahulu'];

            return null;
        }

        $nips = array_values(array_unique(array_filter(array_map('strval', $this->selected))));
        $data = $this->tableListQuery()->whereIn('nip', $nips)->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();

        $ws->fromArray([[
            'NIP',
            'Nama',
            'Holding',
            'Department',
            'Division',
            'Position',
            'Status',
            'Tanggal Join',
            'Email',
            'No HP',
        ]], null, 'A1');

        $row = 2;
        foreach ($data as $e) {
            $join = $e->tanggal_join ? Carbon::parse($e->tanggal_join)->format('Y-m-d') : '';

            $ws->fromArray([
                (string) ($e->nip ?? ''),
                (string) ($e->nama ?? ''),
                (string) ($e->holding_name ?? ''),
                (string) ($e->department_name ?? ''),
                (string) ($e->division_name ?? ''),
                (string) ($e->position_title ?? ''),
                (string) ($e->employee_status ?? ''),
                (string) $join,
                (string) ($e->email ?? ''),
                (string) ($e->no_hp ?? ''),
            ], null, 'A'.$row++);
        }

        $filename = "Emp_Employees_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new Xlsx($sheet);
        $tmp = tempnam(sys_get_temp_dir(), 'Emp_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    /* ===================== OVERLAY CONTROL ===================== */
    public function openCreate(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin create Employee.'];

            return;
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayKey = null;
    }

    public function openShow(string $nip): void
    {
        $this->overlayMode = 'show';
        $this->overlayKey = $nip;
    }

    public function openEdit(string $nip): void
    {
        if (! $this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin update Employee.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayKey = $nip;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayKey']);
    }

    /* ===================== OPTIONS ===================== */
    protected function holdingOptions(): array
    {
        return Holding::query()
            ->orderBy('name')
            ->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [(string) $h->id => ($h->name.($h->alias ? ' - '.$h->alias : ''))])
            ->toArray();
    }

    protected function positionOptions(): array
    {
        // tanpa model juga aman
        return DB::table('emp_positions')
            ->orderBy('level')
            ->orderBy('title')
            ->get(['id', 'level', 'title'])
            ->mapWithKeys(fn ($p) => [(string) $p->id => ('L'.$p->level.' - '.$p->title)])
            ->toArray();
    }

    protected function statusOptions(): array
    {
        return [
            '' => 'Semua',
            'PKWT' => 'PKWT',
            'Karyawan Tetap' => 'Karyawan Tetap',
            'RESIGN' => 'RESIGN',
        ];
    }

    /* ===================== EVENTS ===================== */
    #[On('emp-employee-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('emp-employee-created')]
    public function handleCreated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Employee berhasil ditambahkan.'];
        $this->resetPage();
    }

    #[On('emp-employee-open-edit')]
    public function handleOpenEditFromShow(string $nip): void
    {
        $this->openEdit($nip);
    }

    #[On('emp-employee-updated')]
    public function handleUpdated(): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Employee berhasil diperbarui.'];
        $this->resetPage();
    }

    public function render()
    {
        $rows = $this->tableListQuery()->paginate($this->perPage);

        $visible = $rows->getCollection()
            ->pluck('nip')
            ->map(fn ($x) => (string) $x)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));

        return view('livewire.holdings.hq.sdm.hr.employee-table', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdingOptions' => $this->holdingOptions(),
            'positionOptions' => $this->positionOptions(),
            'statusOptions' => $this->statusOptions(),
            'rows' => $rows,
        ])->layout('components.sccr-layout');
    }
}
