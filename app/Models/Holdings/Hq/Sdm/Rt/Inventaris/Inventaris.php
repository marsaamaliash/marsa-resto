<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use App\Models\Holding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventaris extends Model
{
    use SoftDeletes;

    protected $table = 'inventaris';

    protected $primaryKey = 'kode_label';

    public $incrementing = false;

    protected $keyType = 'string';

    // tabel inventaris tidak punya created_at/updated_at => false
    public $timestamps = false; // <- ini wajib kalau tabel tidak punya created_at & updated_at

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'kode_label',
        'nama_barang',
        'description',
        'foto',
        'dokumen',

        'created_by',
        'updated_by',

        'ab',
        'cd',
        'ef',
        'gh',
        'ijk',
        'no_urut',
        'Bulan',
        'Tahun',
        'status',
        'tanggal_status',

        'lifecycle_status',
        'deleted_at',
        'deleted_by',
        'deleted_reason',
    ];

    protected $casts = [
        'tanggal_status' => 'date',
        'deleted_at' => 'datetime',
        'Bulan' => 'integer',
        'Tahun' => 'integer',
        'no_urut' => 'integer',
    ];

    /**
     * AB => holdings.inv_code
     */
    public function holdingNya()
    {
        return $this->belongsTo(Holding::class, 'ab', 'inv_code');
    }

    /**
     * NOTE:
     * inv_lokasi & inv_ruangan secara DB idealnya composite key (holding_kode + kode),
     * tapi untuk kebutuhan tampilan sederhana tetap bisa pakai 'cd'/'ef' ke 'kode'.
     * (Kalau kamu mau benar-benar strict, nanti kita buat relasi manual scope.)
     */
    public function lokasiNya()
    {
        return $this->belongsTo(Inv_Lokasi::class, 'cd', 'kode');
    }

    public function ruanganNya()
    {
        return $this->belongsTo(Inv_Ruangan::class, 'ef', 'kode');
    }

    public function jenis_barangNya()
    {
        return $this->belongsTo(Inv_Jenis_Barang::class, 'gh', 'kode');
    }
}
