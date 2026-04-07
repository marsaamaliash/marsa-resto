<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;

class InventarisList extends Model
{
    protected $table = 'v_inventaris_lengkap';

    protected $primaryKey = 'kode_label';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    // Status
    public function getStatusBadgeTypeAttribute(): string
    {
        return match ($this->status) {
            'Baik' => 'success',
            'Dalam Perbaikan' => 'warning',
            'Rusak', 'Hilang' => 'danger',
            default => 'default',
        };
    }

    // FOTO
    public function getFotoDiskPathAttribute(): ?string
    {
        return $this->foto ? public_path('SDM/inventaris/foto/'.$this->foto) : null;
    }

    public function getHasFotoAttribute(): bool
    {
        return $this->foto && $this->foto_disk_path && file_exists($this->foto_disk_path);
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? asset('SDM/inventaris/foto/'.$this->foto) : null;
    }

    // DOKUMEN
    public function getDokumenDiskPathAttribute(): ?string
    {
        return $this->dokumen ? public_path('SDM/inventaris/dokumen/'.$this->dokumen) : null;
    }

    public function getHasDokumenAttribute(): bool
    {
        return $this->dokumen && $this->dokumen_disk_path && file_exists($this->dokumen_disk_path);
    }

    public function getDokumenUrlAttribute(): ?string
    {
        return $this->dokumen ? asset('SDM/inventaris/dokumen/'.$this->dokumen) : null;
    }
}
