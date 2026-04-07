<?php

namespace App\Livewire\Holdings\Hq\Sdm\Hr;

use App\Models\Department;
use App\Models\Division;
use App\Models\Holding;
use App\Models\Holdings\Hq\Sdm\Hr\Emp_Employee;
use App\Models\Holdings\Hq\Sdm\Hr\Emp_JobTitle;
use App\Models\Holdings\Hq\Sdm\Hr\Emp_Position;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EmployeeEdit extends Component
{
    public string $nip = '';

    public string $nip_original = '';

    public ?string $gelar_depan = null;

    public string $nama = '';

    public ?string $gelar_belakang = null;

    public $holding_id = null;

    public $department_id = null;

    public $division_id = null;

    public $position_id = null;

    public $job_title_id = null;

    public ?string $tanggal_join = null;

    public string $employee_status = 'PKWT';

    public ?string $employee_code = null;

    public ?string $alamat_asal = null;

    public ?string $kota_asal = null;

    public ?string $alamat_domisili = null;

    public ?string $kota_domisili = null;

    public ?string $jenis_kelamin = null;

    public ?string $status_perkawinan = null;

    public ?string $agama = null;

    public ?string $gol_darah = null;

    public ?string $tempat_lahir = null;

    public ?string $tanggal_lahir = null;

    public ?string $pendidikan = null;

    public ?string $jurusan = null;

    public ?string $email = null;

    public ?string $no_hp = null;

    public ?string $no_ektp = null;

    public ?string $npwp = null;

    public ?string $kis = null;

    public ?string $bpjs_tk = null;

    public ?string $no_rekening = null;

    public ?string $pemilik_rekening = null;

    public ?string $nama_bank = null;

    public bool $showCancelConfirm = false;

    // overlay support (biar konsisten dengan Inventaris)
    public bool $asOverlay = false;

    public function mount(?string $nip = null, bool $asOverlay = false): void
    {
        $this->asOverlay = $asOverlay;

        $key = trim((string) $nip);
        abort_unless($key !== '', 404, 'NIP tidak ditemukan');

        $this->loadEmployee($key);
    }

    private function loadEmployee(string $nip): void
    {
        $e = Emp_Employee::query()->where('nip', $nip)->firstOrFail();

        $this->nip_original = (string) $e->nip;
        $this->nip = (string) $e->nip;

        $this->gelar_depan = $e->gelar_depan;
        $this->nama = (string) ($e->nama ?? '');
        $this->gelar_belakang = $e->gelar_belakang;

        $this->holding_id = $e->holding_id;
        $this->department_id = $e->department_id;
        $this->division_id = $e->division_id;
        $this->position_id = $e->position_id;
        $this->job_title_id = $e->job_title_id;

        $this->tanggal_join = $e->tanggal_join
            ? Carbon::parse($e->tanggal_join)->format('Y-m-d')
            : null;

        $this->employee_status = (string) ($e->employee_status ?? 'PKWT');
        $this->employee_code = $e->employee_code;

        $this->alamat_asal = $e->alamat_asal;
        $this->kota_asal = $e->kota_asal;
        $this->alamat_domisili = $e->alamat_domisili;
        $this->kota_domisili = $e->kota_domisili;

        $this->jenis_kelamin = $e->jenis_kelamin;
        $this->status_perkawinan = $e->status_perkawinan;
        $this->agama = $e->agama;
        $this->gol_darah = $e->gol_darah;

        $this->tempat_lahir = $e->tempat_lahir;

        $this->tanggal_lahir = $e->tanggal_lahir
            ? Carbon::parse($e->tanggal_lahir)->format('Y-m-d')
            : null;

        $this->pendidikan = $e->pendidikan;
        $this->jurusan = $e->jurusan;

        $this->email = $e->email;
        $this->no_hp = $e->no_hp;
        $this->no_ektp = $e->no_ektp;
        $this->npwp = $e->npwp;
        $this->kis = $e->kis;
        $this->bpjs_tk = $e->bpjs_tk;

        $this->no_rekening = $e->no_rekening;
        $this->pemilik_rekening = $e->pemilik_rekening;
        $this->nama_bank = $e->nama_bank;
    }

    protected function rules(): array
    {
        return [
            'nip' => [
                'required', 'string', 'max:30',
                Rule::unique('employees', 'nip')->ignore($this->nip_original, 'nip'),
            ],

            'gelar_depan' => ['nullable', 'string'],
            'nama' => ['required', 'string', 'max:255'],
            'gelar_belakang' => ['nullable', 'string'],

            'holding_id' => ['required', 'exists:holdings,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'division_id' => ['required', 'exists:divisions,id'],

            // sesuaikan ke tabel master baru
            'position_id' => ['required', 'exists:emp_positions,id'],
            'job_title_id' => ['required', 'exists:emp_job_titles,id'],

            'tanggal_join' => ['required', 'date_format:Y-m-d'],
            'employee_status' => ['required', Rule::in(['PKWT', 'Karyawan Tetap', 'RESIGN'])],

            'employee_code' => ['nullable', 'string'],

            'alamat_asal' => ['nullable', 'string'],
            'kota_asal' => ['nullable', 'string'],
            'alamat_domisili' => ['nullable', 'string'],
            'kota_domisili' => ['nullable', 'string'],

            'jenis_kelamin' => ['required', Rule::in(['Laki-laki', 'Perempuan'])],
            'status_perkawinan' => ['required', Rule::in(['Menikah', 'Belum Menikah', 'Cerai Hidup', 'Cerai Mati'])],
            'agama' => ['required', Rule::in(['Islam', 'Kristen', 'Hindu', 'Buddha', 'Konghuchu', 'Kepercayaan', 'Tidak Punya'])],
            'gol_darah' => ['required', Rule::in(['A', 'B', 'AB', 'O', 'TIDAK TAHU'])],

            'tempat_lahir' => ['nullable', 'string'],
            'tanggal_lahir' => ['nullable', 'date_format:Y-m-d'],

            'pendidikan' => ['nullable', 'string'],
            'jurusan' => ['nullable', 'string'],

            'email' => ['nullable', 'email'],
            'no_hp' => ['nullable', 'string'],
            'no_ektp' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string'],
            'kis' => ['nullable', 'string'],
            'bpjs_tk' => ['nullable', 'string'],

            'no_rekening' => ['nullable', 'string'],
            'pemilik_rekening' => ['nullable', 'string'],
            'nama_bank' => ['nullable', 'string'],
        ];
    }

    public function update(): mixed
    {
        $this->validate();

        Emp_Employee::where('nip', $this->nip_original)->update([
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
            'nama_bank' => $this->nama_bank,
        ]);

        $this->nip_original = $this->nip;

        // overlay pattern (Inventaris style)
        if ($this->asOverlay) {
            $this->dispatch('emp-employee-updated', nip: (string) $this->nip);
            $this->dispatch('emp-employee-overlay-close');

            return null;
        }

        session()->flash('success', 'Data karyawan berhasil diperbarui!');

        return null;
    }

    public function confirmCancel(): void
    {
        $this->showCancelConfirm = true;
    }

    public function cancel(): mixed
    {
        $this->showCancelConfirm = false;

        if ($this->asOverlay) {
            $this->dispatch('emp-employee-overlay-close');

            return null;
        }

        return redirect()->route('holdings.hq.sdm.hr.employee-table');
    }

    public function render()
    {
        $view = view('livewire.holdings.hq.sdm.hr.employee-edit', [
            'holdings' => Holding::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
            'positions' => Emp_Position::orderBy('level')->orderBy('title')->get(),
            'job_titles' => Emp_JobTitle::orderBy('name')->get(),
        ]);

        return $this->asOverlay
            ? $view
            : $view->layout('components.sccr-layout');
    }
}
