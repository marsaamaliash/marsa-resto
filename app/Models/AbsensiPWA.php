<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiPWA extends Model
{
    protected $table = 'absensi';

    protected $primaryKey = 'id_absensi';

    public $incrementing = true;

    protected $fillable = [
        'nik',
        'id_holding',
        'tanggal',
        'jenis',
        'jam',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'nik', 'nik');
    }

    public function holding()
    {
        return $this->belongsTo(Holding::class, 'id_holding', 'id_holding');
    }
}
