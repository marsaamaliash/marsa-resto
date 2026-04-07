<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inv_Ruangan extends Model
{
    protected $table = 'inv_ruangan';

    protected $primaryKey = 'kode';

    public $incrementing = false; // karena PK varchar

    protected $keyType = 'string';

    protected $fillable = [
        'holding_kode', 'lokasi_kode', 'kode', 'nama_ruang',
    ];

    public $timestamps = false; // <- ini wajib kalau tabel tidak punya created_at & updated_at

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(
            Inv_Lokasi::class,
            'lokasi_kode', // FK
            'kode'         // PK
        );
    }
}
