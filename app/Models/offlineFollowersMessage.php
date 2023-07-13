<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class offlineFollowersMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'message',
        'isRead'
    ];
}
