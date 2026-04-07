<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'journal_no', 'holding_id', 'department_id', 'division_id',
        'date', 'description', 'created_by', 'approved_by', 'status',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
