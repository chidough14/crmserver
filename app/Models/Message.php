<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'subject', 'message','receiver_id', 'sender_id', 'isRead', 'quill_message', 'files'
    ];

    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
