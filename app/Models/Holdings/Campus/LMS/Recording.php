<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    protected $fillable = ['webinar_id', 'title', 'file_path', 'duration'];

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }
}
