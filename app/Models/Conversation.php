<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_string', 'user_id', 'recipient_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
