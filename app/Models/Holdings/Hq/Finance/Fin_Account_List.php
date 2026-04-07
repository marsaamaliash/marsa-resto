<?php

namespace App\Models\Holdings\Hq\Finance;

use Illuminate\Database\Eloquent\Model;

class Fin_Account_List extends Model
{
    protected $table = 'v_fin_accounts';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $guarded = [];
}
