<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['holding_id', 'name', 'code'];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
