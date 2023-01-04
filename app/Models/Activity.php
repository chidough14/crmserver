<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'label', 'assignedTo','description', 'type', 'company_id', 'user_id', 'earningEstimate', 'probability'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'activity_products', 'activity_id', 'product_id')->withPivot('quantity');
    }
}
