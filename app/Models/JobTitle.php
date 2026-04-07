<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_job_title')
            ->withPivot('holding_id')
            ->withTimestamps();
    }
}
