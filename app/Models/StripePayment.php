<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'products','subtotal', 'total', 'shipping', 'delivery_status', 'payment_status', 'activity_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
