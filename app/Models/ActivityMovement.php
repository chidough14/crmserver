<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id', 'movement'
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

}
