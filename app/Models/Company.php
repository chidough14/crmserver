<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','email', 'phone', 'address', 'contactPerson'
    ];

    public function lists()
    {
        return $this->belongsToMany(CompanyList::class, 'company_list_companies', 'company_id', 'list_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
