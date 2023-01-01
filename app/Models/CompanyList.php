<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','description', 'type', 'user_id'
    ];

      /**
     * The companies that belong to the list.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_list_company');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
