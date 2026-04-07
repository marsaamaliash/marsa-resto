<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inv_Holding extends Model
{
    protected $table = 'inv_holding';

    protected $primaryKey = 'kode';

    public $incrementing = false; // karena PK varchar

    protected $keyType = 'string';

    protected $fillable = [
        'kode', 'holding', 'nama_holding',
    ];

    public $timestamps = false; // <- ini wajib kalau tabel tidak punya created_at & updated_at
}
