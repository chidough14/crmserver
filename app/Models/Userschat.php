<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userschat extends Model
{
    use HasFactory;

    protected $fillable = [
        'message', 'user_id', 'recipient_id', 'conversation_id', 'files'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
