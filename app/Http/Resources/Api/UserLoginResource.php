<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
class UserLoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'status'=>$this->status,            
            'user_type' => $this->user_type,
            'token' => $this->token,
            'age' => $this->age,
            'gender' => $this->gender,
            'country' => $this->country,
            'hobbies' => $this->hobbies,
            'interest' => $this->interest,
            'profile_image' => $this->profile_image,
            
        ];
    }
}
