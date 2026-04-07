<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use App\Models\Department;
use App\Models\Division;
use App\Models\Holding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emp_Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = [
        'tanggal_join' => 'date',
        'tanggal_lahir' => 'date',
        'holding_id' => 'integer',
        'department_id' => 'integer',
        'division_id' => 'integer',
        'position_id' => 'integer',
        'job_title_id' => 'integer',
    ];

    protected $fillable = [
        'nip',
        'gelar_depan',
        'nama',
        'gelar_belakang',

        'holding_id',
        'department_id',
        'division_id',
        'position_id',
        'job_title_id',
        'job_title',

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

        // optional:
        // 'foto',
        // 'employee_finger',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class, 'holding_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function position()
    {
        return $this->belongsTo(Emp_Position::class, 'position_id');
    }

    public function jobTitleMaster()
    {
        return $this->belongsTo(Emp_JobTitle::class, 'job_title_id');
    }

    public function jobTitles()
    {
        // pivot sudah diprefix emp_
        return $this->belongsToMany(Emp_JobTitle::class, 'emp_employee_job_title', 'employee_nip', 'job_title_id')
            ->withPivot(['holding_id'])
            ->withTimestamps();
    }
}
