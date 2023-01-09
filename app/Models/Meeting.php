<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meetingName', 'meetingId', 'meetingType','invitedUsers', 'meetingDate', 'maxUsers', 'user_id', 'status', 'event_id'
    ];


    protected $casts = [
        'invitedUsers' => 'array'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
