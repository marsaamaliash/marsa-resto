<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;

class Rst_MasterLokasi extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'locations';

    protected $primaryKey = 'id';

    protected $guarded = [
        
    ];
}
