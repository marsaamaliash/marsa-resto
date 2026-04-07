<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inv_Lokasi extends Model
{
    protected $table = 'inv_lokasi';

    // protected $primaryKey = ['kode', 'holding_kode'];
    protected $primaryKey = 'kode';

    public $incrementing = false; // karena PK varchar

    protected $keyType = 'string';

    protected $fillable = [
        'holding_kode', 'kode', 'lokasi',
    ];

    public $timestamps = false; // <- ini wajib kalau tabel tidak punya created_at & updated_at

    public function ruangans(): HasMany
    {
        return $this->hasMany(
            Inv_Ruangan::class,
            'lokasi_kode', // FK di inv_ruangan
            'kode'         // PK di inv_lokasi
        );
    }
}
