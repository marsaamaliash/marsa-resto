<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use Illuminate\Database\Eloquent\Model;

class Emp_Employee_List extends Model
{
    protected $table = 'v_emp_employees';

    protected $primaryKey = 'nip';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    // Status
    public function getStatusBadgeTypeAttribute(): string
    {
        return match ($this->status) {
            'Karyawan Tetap' => 'success',
            'RESIGN' => 'danger',
            default => 'default',
        };
    }
}
