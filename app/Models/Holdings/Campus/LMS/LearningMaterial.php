<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class LearningMaterial extends Model
{
    protected $fillable = ['room_id', 'title', 'description', 'file_path', 'type'];

    public function room()
    {
        return $this->belongsTo(LmsRoom::class);
    }
}
