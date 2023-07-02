<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
    use HasFactory;

    protected $fillable = [
        'followee_id',
        'follower_id'
    ];

    // Define the relationship where a follower belongs to a user being followed
    public function followee()
    {
        return $this->belongsTo(User::class, 'followee_id');
    }

    // Define the relationship where a follower belongs to a user who is following
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
}
