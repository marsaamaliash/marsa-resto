<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holding;
use App\Models\Position;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmployeeTable extends Component
{
    use WithPagination;

    // Filters
    public $search = '';

    public $holding = '';

    public $position = '';

    public $tanggal_join = '';

    // Pagination & sorting
    public $perPage = 10;

    public $sortField = 'nip';

    public $sortDirection = 'asc';

    // Selection and visible keys
    public $selectedEmployees = [];

    public $selectAll = false;

    public $visibleNips = [];

    // Detail/Edit Modal
    public $showModal = false;

    public $modalMode = 'detail';

    public $selectedEmployee = null;

    // Tambah Modal
    public $showCreateModal = false;

    // Quick Add Modal
    public $showCreateAddModal = false;

    protected $queryString = [
        'search', 'holding', 'position', 'tanggal_join',
        'perPage', 'sortField', 'sortDirection',
    ];

    protected $listeners = [
        'employeeCreated' => 'handleEmployeeCreated',
        'closeCreateModal' => 'closeCreateModal',
    ];

    // Tambah Data
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    // Quick Add
    public function openCreateAddModal()
    {
        $this->showCreateAddModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function closeCreateAddModal()
    {
        $this->showCreateAddModal = false;
    }

    public function handleEmployeeCreated()
    {
        $this->closeCreateModal();
        $this->resetPage();
        session()->flash('message', 'Karyawan berhasil ditambahkan.');
    }

    public function handleEmployeeCreatedAdd()
    {
        $this->closeCreateAddModal();
        $this->resetPage();
        session()->flash('message', 'Karyawan berhasil ditambahkan.');
    }

    // Reset page when filters change
    public function updating($property)
    {
        if (in_array($property, ['search', 'holding', 'position', 'tanggal_join', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function applyFilter()
    {
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedEmployees = [];
    }

    public function clearFilters()
    {
        $this->reset(['search', 'holding', 'position', 'tanggal_join']);
        $this->resetPage();
        $this->selectAll = false;
        $this->selectedEmployees = [];
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        // Ambil NIP yang terlihat pada halaman saat ini menggunakan query builder
        // Kita harus panggil $this->getPage() untuk mendapatkan nomor halaman yang aman
        $nipsOnCurrentPage = $this->tableListQuery()
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('nip')
            ->map(fn ($n) => (string) $n) // Konfirmasi NIP sebagai string
            ->toArray();

        if ($value) {
            // SELECT ALL: Gabungkan NIP halaman saat ini ke dalam array yang sudah terpilih
            $this->selectedEmployees = array_unique(array_merge($this->selectedEmployees, $nipsOnCurrentPage));
        } else {
            // UNSELECT ALL: Hapus NIP halaman saat ini dari array yang sudah terpilih
            $this->selectedEmployees = array_values(array_diff($this->selectedEmployees, $nipsOnCurrentPage));
        }
    }

    public function updatedSelectedEmployees()
    {
        // Konversi NIP yang baru dipilih ke string
        $this->selectedEmployees = array_map('strval', (array) $this->selectedEmployees);

        if (empty($this->visibleNips)) {
            $this->selectAll = false;

            return;
        }

        // Cek apakah NIP yang terlihat adalah subset dari NIP yang dipilih
        $this->selectAll = count(array_intersect($this->visibleNips, $this->selectedEmployees)) === count($this->visibleNips);
    }

    // Detail/Edit Modal
    public function openModal($nip, $mode = 'detail')
    {
        $this->selectedEmployee = Employee::with(['holding', 'department', 'division', 'position', 'jobTitles'])
            ->where('nip', $nip)
            ->first();

        $this->modalMode = $mode;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedEmployee = null;
    }

    // Delete
    public function deleteSingle($nip)
    {
        Employee::where('nip', $nip)->delete();
        $this->selectedEmployees = array_values(array_filter($this->selectedEmployees, fn ($v) => $v !== (string) $nip));
        $this->selectAll = false;
        $this->resetPage();

        session()->flash('message', 'Karyawan berhasil dihapus.');
    }

    public function deleteSelected()
    {
        if (! empty($this->selectedEmployees)) {
            Employee::whereIn('nip', $this->selectedEmployees)->delete();
            $this->selectedEmployees = [];
            $this->selectAll = false;
            $this->resetPage();
            session()->flash('message', 'Karyawan terpilih berhasil dihapus.');
        }
    }

    // Export Excel - All Filtered Data
    public function exportFiltered()
    {
        $employees = $this->tableListQuery()->get();

        return $this->generateExcel($employees, 'filtered');
    }

    // Export Excel - Only Selected Data
    public function exportSelected()
    {
        if (empty($this->selectedEmployees)) {
            session()->flash('error', 'Tidak ada karyawan yang dipilih untuk di-export.');

            return;
        }

        $employees = Employee::with(['holding', 'department', 'division', 'position'])
            ->whereIn('nip', $this->selectedEmployees)
            ->get();

        return $this->generateExcel($employees, 'selected');
    }

    private function generateExcel($employees, $type = 'filtered')
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Header row
        $headers = ['NIP', 'Nama', 'Holding', 'Departemen', 'Divisi', 'Posisi', 'Tanggal Join', 'Status Karyawan', 'Email', 'No HP'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F59E0B']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

        // Data rows
        $row = 2;
        foreach ($employees as $employee) {
            $namaLengkap = trim(
                ($employee->gelar_depan ? $employee->gelar_depan.' ' : '').
                $employee->nama.
                ($employee->gelar_belakang ? ', '.$employee->gelar_belakang : '')
            );

            $sheet->fromArray([
                $employee->nip,
                $namaLengkap,
                $employee->holding->name ?? '-',
                $employee->department->name ?? '-',
                $employee->division->name ?? '-',
                $employee->position->title ?? '-',
                $employee->tanggal_join ? \Carbon\Carbon::parse($employee->tanggal_join)->format('d-m-Y') : '-',
                $employee->employee_status ?? '-',
                $employee->email ?? '-',
                $employee->no_hp ?? '-',
            ], null, 'A'.$row);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate filename
        $timestamp = now()->format('Y-m-d_His');
        $filename = "Employee_Data_{$timestamp}.xlsx";

        // Save file
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'employee_export_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    // Query builder
    private function tableListQuery()
    {
        return EmpEmployeeList::query() // table: v_emp_employees
            ->when($this->search, function ($q) {
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
            ->when($this->holding, fn ($q) => $q->where('holding_id', $this->holding))
            ->when($this->position, fn ($q) => $q->where('position_id', $this->position))
            ->when($this->tanggal_join, fn ($q) => $q->whereDate('tanggal_join', $this->tanggal_join))
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('nip', 'asc');
    }

    public function render()
    {
        $employees = $this->tableListQuery()->paginate($this->perPage);

        // 1. Ambil NIP yang terlihat dan konversi ke string
        $this->visibleNips = $employees->pluck('nip')->map(fn ($n) => (string) $n)->toArray();

        // 2. Jaminan Konsistensi Tipe Data untuk selectedEmployees
        $this->selectedEmployees = array_map('strval', (array) $this->selectedEmployees);

        // 3. Sinkronisasi status $selectAll saat render
        $this->selectAll = count(array_intersect($this->visibleNips, $this->selectedEmployees)) === count($this->visibleNips) && count($this->visibleNips) > 0;

        $holdingOptions = ['' => '-Holding-'] + Holding::pluck('name', 'id')->toArray();
        $positions = ['' => '-Position-'] + Position::pluck('title', 'id')->toArray();
        $departments = Department::pluck('name', 'id')->toArray();

        return view('livewire.holdings.hq.sdm.hr.employee-table', [
            'employees' => $employees,
            'holdingOptions' => $holdingOptions,
            'positions' => $positions,
            'departments' => $departments,
            'showCreateModal' => $this->showCreateModal,
            'showCreateAddModal' => $this->showCreateAddModal,
        ])->layout('components.sccr-layout');
    }
}
