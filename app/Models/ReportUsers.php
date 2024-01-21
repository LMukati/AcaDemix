<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportUsers extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    
    
    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function reportUser()
    {
        return $this->hasOne(User::class,'id','report_user_id');
    }
}
