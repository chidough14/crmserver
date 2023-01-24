<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'calendar_mode','dashboard_mode', 'currency_mode', 'product_sales_mode'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
