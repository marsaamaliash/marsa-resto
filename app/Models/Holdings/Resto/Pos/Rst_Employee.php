<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rst_Employee extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'employees';

    protected $fillable = [
        'employee_number',
        'name',
        'department',
        'position',
        'daily_allowance',
        'is_active',
    ];

    protected $casts = [
        'daily_allowance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function lunchTransactions(): HasMany
    {
        return $this->hasMany(Rst_EmployeeLunchTransaction::class, 'employee_number', 'employee_number');
    }

    public function getTodayUsage(): float
    {
        return (float) Rst_EmployeeLunchTransaction::where('employee_number', $this->employee_number)
            ->whereDate('created_at', today())
            ->sum('allowance_used');
    }

    public function getTodayRemaining(): float
    {
        return (float) $this->daily_allowance - $this->getTodayUsage();
    }
}
