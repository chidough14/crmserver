<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = ['comment_id', 'vote_type', 'user_id'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
}
