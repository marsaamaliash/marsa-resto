<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $fillable = [
        'holding_id', 'account_id', 'type', 'amount',
        'date', 'reference', 'description', 'status', 'journal_id',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }
}
