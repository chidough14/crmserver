<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyList extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name','description', 'type', 'user_id'
    ];

      /**
     * The companies that belong to the list.
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_list_companies', 'list_id', 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
