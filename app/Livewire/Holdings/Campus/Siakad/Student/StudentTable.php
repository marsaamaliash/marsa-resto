<?php

namespace App\Livewire\Holdings\Campus\Siakad\Student;

use App\Models\Holdings\Campus\Siakads\StudyProgram;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentTable extends Component
{
    use WithPagination;

    // Filters
    public $search = '';

    public $program_id = '';

    public $academic_year = '';

    // Pagination & sorting
    public $perPage = 10;

    public $sortField = 'nim';

    public $sortDirection = 'asc';

    // Selection
    public $selectedStudents = [];

    public $selectAll = false;

    public $visibleNims = [];

    // Detail Modal
    public $showModal = false;

    public $modalMode = 'detail';

    public $selectedStudent = null;

    // Create Modal
    public $showCreateModal = false;

    protected $queryString = [
        'search', 'program_id', 'academic_year',
        'perPage', 'sortField', 'sortDirection',
    ];

    protected $listeners = [
        'studentCreated' => 'handleStudentCreated',
        'closeCreateModal' => 'closeCreateModal',
    ];

    // ─────────────────────────────────────────────
    // Modal Create
    // ─────────────────────────────────────────────
    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function handleStudentCreated()
    {
        $this->closeCreateModal();
        $this->resetPage();
        session()->flash('message', 'Mahasiswa berhasil ditambahkan.');
    }

    // ─────────────────────────────────────────────
    // Livewire Updating Hooks
    // ─────────────────────────────────────────────
    public function updating($property)
    {
        if (in_array($property, ['search', 'program_id', 'academic_year', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function applyFilter()
    {
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function clearFilters()
    {
        $this->reset(['search', 'program_id', 'academic_year']);
        $this->resetPage();
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    // ─────────────────────────────────────────────
    // Sorting
    // ─────────────────────────────────────────────
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

    // ─────────────────────────────────────────────
    // Checkbox
    // ─────────────────────────────────────────────
    public function updatedSelectAll($value)
    {
        $nimsOnPage = $this->studentsQuery()
            ->forPage($this->getPage(), $this->perPage)
            ->pluck('nim')
            ->map(fn ($n) => (string) $n)
            ->toArray();

        if ($value) {
            $this->selectedStudents = array_unique(
                array_merge($this->selectedStudents, $nimsOnPage)
            );
        } else {
            $this->selectedStudents = array_values(
                array_diff($this->selectedStudents, $nimsOnPage)
            );
        }
    }

    public function updatedSelectedStudents()
    {
        $this->selectedStudents = array_map('strval', (array) $this->selectedStudents);

        if (empty($this->visibleNims)) {
            $this->selectAll = false;

            return;
        }

        $this->selectAll =
            count(array_intersect($this->visibleNims, $this->selectedStudents))
            === count($this->visibleNims);
    }

    // ─────────────────────────────────────────────
    // Detail Modal
    // ─────────────────────────────────────────────
    public function openModal($nim, $mode = 'detail')
    {
        $this->selectedStudent = Student::with(['program', 'faculty'])
            ->where('nim', $nim)
            ->first();

        $this->modalMode = $mode;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedStudent = null;
    }

    // ─────────────────────────────────────────────
    // Delete
    // ─────────────────────────────────────────────
    public function deleteSingle($nim)
    {
        Student::where('nim', $nim)->delete();

        $this->selectedStudents = array_values(
            array_filter($this->selectedStudents, fn ($v) => $v !== (string) $nim)
        );

        $this->selectAll = false;
        $this->resetPage();

        session()->flash('message', 'Mahasiswa berhasil dihapus.');
    }

    public function deleteSelected()
    {
        if (! empty($this->selectedStudents)) {
            Student::whereIn('nim', $this->selectedStudents)->delete();

            $this->selectedStudents = [];
            $this->selectAll = false;
            $this->resetPage();

            session()->flash('message', 'Mahasiswa terpilih berhasil dihapus.');
        }
    }

    // ─────────────────────────────────────────────
    // Export Excel
    // ─────────────────────────────────────────────
    public function exportFiltered()
    {
        $students = $this->studentsQuery()->get();

        return $this->generateExcel($students);
    }

    public function exportSelected()
    {
        if (empty($this->selectedStudents)) {
            session()->flash('error', 'Tidak ada mahasiswa yang dipilih.');

            return;
        }

        $students = Student::with('program')
            ->whereIn('nim', $this->selectedStudents)
            ->get();

        return $this->generateExcel($students);
    }

    private function generateExcel($students)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'NIM', 'Nama Lengkap', 'Program Studi', 'Tahun Akademik',
            'Kelas', 'Email', 'No HP',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($students as $s) {
            $sheet->fromArray([
                $s->nim,
                $s->full_name,
                $s->program->program_name ?? '-',
                $s->academic_year,
                $s->class_group,
                $s->email,
                $s->phone,
            ], null, 'A'.$row);

            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Student_Data_'.now()->format('Ymd_His').'.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 's_export_');

        (new Xlsx($spreadsheet))->save($temp);

        return response()->download($temp, $filename)->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────
    // Query Builder
    // ─────────────────────────────────────────────
    private function studentsQuery()
    {
        return Student::query()
            ->with(['program', 'faculty'])
            ->when($this->search, function ($q) {
                $q->where('nim', 'like', "%$this->search%")
                    ->orWhere('first_name', 'like', "%$this->search%")
                    ->orWhere('last_name', 'like', "%$this->search%")
                    ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$this->search%"]);
            })
            ->when($this->program_id, fn ($q) => $q->where('program_id', $this->program_id))
            ->when($this->academic_year, fn ($q) => $q->where('academic_year', $this->academic_year))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    // ─────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────
    public function render()
    {
        $students = $this->studentsQuery()->paginate($this->perPage);

        $this->visibleNims = $students->pluck('nim')->map(fn ($n) => (string) $n)->toArray();

        $this->selectedStudents = array_map('strval', (array) $this->selectedStudents);

        $this->selectAll =
            count(array_intersect($this->visibleNims, $this->selectedStudents))
            === count($this->visibleNims) &&
            count($this->visibleNims) > 0;

        return view('livewire.holdings.campus.siakad.student.students-table', [
            'students' => $students,
            'programs' => StudyProgram::pluck('program_name', 'id')->toArray(),
        ])->layout('components.sccr-layout');
    }
}
