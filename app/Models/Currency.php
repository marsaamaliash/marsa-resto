<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'is_base'];

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
