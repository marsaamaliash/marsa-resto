<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSubcategory extends Model
{
    protected $fillable = ['account_type', 'name', 'code'];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'subcategory_id');
    }
}
