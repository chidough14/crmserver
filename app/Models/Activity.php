<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'label', 'assignedTo','description', 'type', 'company_id', 'user_id', 'earningEstimate', 'probability', 'status', 'decreased_probability'
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
