<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_id', 'account_id', 'debit', 'credit',
        'memo', 'department_id', 'division_id', 'employee_nip',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_nip', 'nip');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
