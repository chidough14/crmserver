<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tc',
        'profile_pic',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function lists()
    {
        return $this->hasMany(CompanyList::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    public function setting()
    {
        return $this->hasOne(Settings::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function stripePayments()
    {
        return $this->hasMany(StripePayment::class);
    }

     // Define the relationship where a user can have many followers
     public function followers()
     {
         return $this->hasMany(Follower::class, 'followee_id');
     }
 
     // Define the relationship where a user can be followed by many users
     public function following()
     {
         return $this->hasMany(Follower::class, 'follower_id');
     }

     public function comments()
     {
         return $this->hasMany(Comment::class);
     }

     
    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function adminchats()
    {
        return $this->hasMany(Adminchat::class);
    }

    public function userschats()
    {
        return $this->hasMany(Userschat::class);
    }

    public function labels()
    {
        return $this->hasMany(Label::class);
    }

    // public function messages()
    // {
    //     return $this->hasMany(Message::class);
    // }
}
