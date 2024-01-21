<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
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


    protected $appends =['hobbies', 'interest'];


    public function getProfileImageAttribute($value)
    {
        if ($value) {
            return asset('/uploads/images/'.$value);
        } else {
            return $value;
        }
    }
    public function getHobbiesAttribute($value)
    {
        $user_category = CategoryUser::select('categories.id','categories.name')->where('category_users.user_id',$this->id)->where('categories.type','hobbies')->leftJoin('categories', 'categories.id', '=', 'category_users.category_id')->get();
        return $user_category;
    }

    public function getInterestAttribute($value)
    {
        $user_category = CategoryUser::select('categories.id','categories.name')->where('category_users.user_id',$this->id)->where('categories.type','interest')->leftJoin('categories', 'categories.id', '=', 'category_users.category_id')->get();
        return $user_category;
    }

}
