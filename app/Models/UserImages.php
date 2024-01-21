<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImages extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'user_images'; 

    public function getImagesAttribute($value)
    {
        if ($value) {
            return asset('/uploads/images/'.$value);
        } else {
            return $value;
        }
    }
}
