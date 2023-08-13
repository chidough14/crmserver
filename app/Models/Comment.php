<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content','parent_id', 'activity_id', 'user_id', 'isDeleted',  'upvotes',  'downvotes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function upvote()
    {
        $this->increment('upvotes');
    }

    public function downvote()
    {
        $this->increment('downvotes');
    }

    // Mutator to set the likersId attribute as an array
    public function setLikersIdAttribute($value)
    {
        $this->attributes['likersId'] = json_encode($value);
    }

    // Accessor to retrieve the likersId attribute as an array
    public function getLikersIdAttribute($value)
    {
        return json_decode($value, true);
    }
}
