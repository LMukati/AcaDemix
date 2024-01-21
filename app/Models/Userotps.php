<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Userotps extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'user_otps'; 
}
