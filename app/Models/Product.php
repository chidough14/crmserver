<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description','price', 'active', 'tax_percentage'
    ];

    public function activities()
    {
        return $this->belongsToMany(Activity::class, 'activity_products', 'product_id', 'activity_id')->withPivot('quantity');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoices_products', 'product_id', 'invoice_id')->withPivot('quantity');
    }
}
