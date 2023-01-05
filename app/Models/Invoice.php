<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no', 'payment_method','billing_address', 'reference', 'status', 'type', 'user_id', 'activity_id'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'invoices_products', 'invoice_id', 'product_id')->withPivot('quantity');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

}
