<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Department;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Holding;
use App\Models\JobTitle;
use App\Models\Position;
use Livewire\Component;

class EmployeeCreate extends Component
{
    public $nip;

    public $gelar_depan;

    public $nama;

    public $gelar_belakang;

    public $holding_id = '';

    public $department_id = '';

    public $division_id = '';

    public $position_id = '';

    public $job_title_id = '';

    public $tanggal_join;

    public $employee_status = 'PKWT';

    public $employee_code;

    public $alamat_asal;

    public $kota_asal;

    public $alamat_domisili;

    public $kota_domisili;

    public $jenis_kelamin = 'Perempuan';

    public $status_perkawinan = 'Belum Menikah';

    public $agama = 'Islam';

    public $tempat_lahir;

    public $tanggal_lahir;

    public $pendidikan;

    public $jurusan;

    public $email;

    public $no_hp;

    public $no_ektp;

    public $kis;

    public $bpjs_tk;

    public $no_rekening;

    public $pemilik_rekening;

    public $nama_bank;

    public $gol_darah = 'TIDAK TAHU';

    public $npwp;

    public $holdings = [];

    public $departments = [];

    public $divisions = [];

    public $positions = [];

    public $job_titles = [];

    public $showCancelConfirm = false;

    protected $rules = [
        'nip' => 'required|string|max:20|unique:employees,nip',
        'gelar_depan' => 'nullable|string',
        'nama' => 'required|string|max:255',
        'gelar_belakang' => 'nullable|string',
        'holding_id' => 'required|exists:holdings,id',
        'department_id' => 'required|exists:departments,id',
        'division_id' => 'required|exists:divisions,id',
        'position_id' => 'required|exists:positions,id',
        'job_title_id' => 'required|exists:job_titles,id',
        'tanggal_join' => ['required', 'date_format:Y-m-d'],
        'employee_status' => 'required|in:PKWT,Karyawan Tetap,RESIGN',
        'employee_code' => 'nullable|string',
        'alamat_asal' => 'nullable|string',
        'kota_asal' => 'nullable|string',
        'alamat_domisili' => 'nullable|string',
        'kota_domisili' => 'nullable|string',
        'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        'status_perkawinan' => 'required|in:Menikah,Belum Menikah,Cerai Hidup,Cerai Mati',
        'agama' => 'required|in:Islam,Kristen,Hindu,Buddha,Konghuchu,Kepercayaan,Tidak Punya',
        'gol_darah' => 'required|in:A,B,AB,O,TIDAK TAHU',
        'tempat_lahir' => 'nullable|string',
        'tanggal_lahir' => ['nullable', 'date_format:Y-m-d'],
        'pendidikan' => 'nullable|string',
        'jurusan' => 'nullable|string',
        'email' => 'nullable|email',
        'no_hp' => 'nullable|string',
        'no_ektp' => 'nullable|string',
        'npwp' => 'nullable|string',
        'kis' => 'nullable|string',
        'bpjs_tk' => 'nullable|string',
        'no_rekening' => 'nullable|string',
        'pemilik_rekening' => 'nullable|string',
        'nama_bank' => 'nullable|string', ];

    protected $messages = [
        'nip.required' => 'NIP wajib diisi.',
        'nip.unique' => 'NIP sudah terdaftar.',
        'nama.required' => 'Nama wajib diisi.',
        'holding_id.required' => 'Holding wajib dipilih.',
        'department_id.required' => 'Department wajib dipilih.',
        'division_id.required' => 'Division wajib dipilih.',
        'position_id.required' => 'Position wajib dipilih.',
        'job_title_id.required' => 'Job Title wajib dipilih.',
        'tanggal_join.required' => 'Tanggal Join wajib diisi.',
    ];

    public function mount()
    {
        $this->holdings = Holding::select('id', 'name')->get();
        $this->departments = Department::select('id', 'name')->get();
        $this->divisions = Division::select('id', 'name')->get();
        $this->positions = Position::select('id', 'title')->get();
        $this->job_titles = JobTitle::select('id', 'name')->get();

        $this->tanggal_join = now()->toDateString(); // format YYYY-MM-DD
    }

    public function save()
    {
        $this->validate();
        // dd();

        Employee::create([
            'nip' => $this->nip,
            'gelar_depan' => $this->gelar_depan,
            'nama' => $this->nama,
            'gelar_belakang' => $this->gelar_belakang,
            'holding_id' => $this->holding_id,
            'department_id' => $this->department_id,
            'division_id' => $this->division_id,
            'position_id' => $this->position_id,
            'job_title_id' => $this->job_title_id,
            'tanggal_join' => $this->tanggal_join,
            'employee_status' => $this->employee_status,
            'employee_code' => $this->employee_code,
            'alamat_asal' => $this->alamat_asal,
            'kota_asal' => $this->kota_asal,
            'alamat_domisili' => $this->alamat_domisili,
            'kota_domisili' => $this->kota_domisili,
            'jenis_kelamin' => $this->jenis_kelamin,
            'status_perkawinan' => $this->status_perkawinan,
            'agama' => $this->agama,
            'gol_darah' => $this->gol_darah,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'pendidikan' => $this->pendidikan,
            'jurusan' => $this->jurusan,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'no_ektp' => $this->no_ektp,
            'npwp' => $this->npwp,
            'kis' => $this->kis,
            'bpjs_tk' => $this->bpjs_tk,
            'no_rekening' => $this->no_rekening,
            'pemilik_rekening' => $this->pemilik_rekening,
            'nama_bank' => $this->nama_bank, ]);

        // Reset input
        $this->reset([
            'nip',
            'gelar_depan',
            'nama',
            'gelar_belakang',
            'holding_id',
            'department_id',
            'division_id',
            'position_id',
            'job_title_id',
            'tanggal_join',
            'employee_status',
            'employee_code',
            'alamat_asal',
            'kota_asal',
            'alamat_domisili',
            'kota_domisili',
            'jenis_kelamin',
            'status_perkawinan',
            'agama',
            'gol_darah',
            'tempat_lahir',
            'tanggal_lahir',
            'pendidikan',
            'jurusan',
            'email',
            'no_hp',
            'no_ektp',
            'npwp',
            'kis',
            'bpjs_tk',
            'no_rekening',
            'pemilik_rekening',
            'nama_bank',
        ]);

        // Livewire v3 syntax: dispatch instead of emit
        $this->dispatch('employeeCreated');

        session()->flash('success', 'Karyawan berhasil ditambahkan.');

        // Close modal
        // $this->dispatch('closeCreateModal');
        $this->dispatch('closeModal');

    }

    public function confirmCancel()
    {
        $this->showCancelConfirm = true;
    }

    public function cancel()
    {
        $this->reset(); // bersihkan form
        $this->dispatch('closeModal'); // tutup modal

        return redirect()->route('holdings.hq.sdm.hr.employee-table'); // kembali ke halaman utama
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.hr.employee-create')->layout('components.sccr-layout');
    }
}
