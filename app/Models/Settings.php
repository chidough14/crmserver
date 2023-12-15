<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'calendar_mode','dashboard_mode', 'currency_mode', 'product_sales_mode', 'top_sales_mode', 'announcements_mode', 'show_weather_widget', 'temperature_mode'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
