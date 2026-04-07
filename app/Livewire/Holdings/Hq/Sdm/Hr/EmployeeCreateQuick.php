<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holding;
use App\Models\Position;
use Livewire\Component;

class EmployeeCreateQuick extends Component
{
    public $nip;

    public $nama;

    public $holding_id;

    public $position_id;

    public $department_id;

    public $tanggal_join;

    public $holdings = [];

    public $positions = [];

    public $departments = [];

    public $showCancelConfirm = false;

    protected $rules = [
        'nip' => 'required|string|max:20|unique:employees,nip',
        'nama' => 'required|string|max:255',
        'holding_id' => 'required|exists:holdings,id',
        'position_id' => 'required|exists:positions,id',
        'department_id' => 'required|exists:departments,id',
        'tanggal_join' => 'required|date',
    ];

    protected $messages = [
        'nip.required' => 'NIP wajib diisi.',
        'nip.unique' => 'NIP sudah terdaftar.',
        'nama.required' => 'Nama wajib diisi.',
        'holding_id.required' => 'Holding wajib dipilih.',
        'position_id.required' => 'Position wajib dipilih.',
        'department_id.required' => 'Department wajib dipilih.',
        'tanggal_join.required' => 'Tanggal Join wajib diisi.',
    ];

    public function mount()
    {
        $this->holdings = Holding::select('id', 'name')->get();
        $this->positions = Position::select('id', 'title')->get();
        $this->departments = Department::select('id', 'name')->get();

        $this->tanggal_join = now()->toDateString(); // format YYYY-MM-DD
    }

    public function save()
    {
        $this->validate();

        Employee::create([
            'nip' => $this->nip,
            'nama' => $this->nama,
            'holding_id' => $this->holding_id,
            'position_id' => $this->position_id,
            'department_id' => $this->department_id,
            'tanggal_join' => $this->tanggal_join,
        ]);

        // Reset input
        $this->reset(['nip', 'nama', 'holding_id', 'position_id', 'department_id', 'tanggal_join']);

        // Livewire v3 syntax: dispatch instead of emit
        $this->dispatch('employeeCreatedAdd');

        session()->flash('success', 'Karyawan berhasil ditambahkan via Quick Add.');

        // Close modal
        $this->dispatch('closeCreateAddModal');
    }

    public function confirmCancel()
    {
        $this->showCancelConfirm = true;
    }

    public function cancel()
    {
        $this->reset(); // bersihkan form
        $this->dispatch('closeCreateAddModal'); // tutup modal

        return redirect()->route('holdings.hq.sdm.hr.employee-table'); // kembali ke halaman utama
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.employee-create-quick')->layout('components.sccr-layout');
    }
}
