<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $successStatus = 200;
    public $code = 200;
    public $status = false;
    public $data = null;
    public $requestdata = [];
    public $message = 'Failed';
   
    
    public function errorValidation($error){
        $datas= [];
        $error = $error->errors()->toArray();
        foreach($error as $key => $err){
            $this->message = $err[0] ; 
            $datas[$key] = $err[0] ; 
            break;
        }
        return $datas;
    }

    public function jsonResponse(){
        $data = [
            'status'    => $this->status,
            'message'   => $this->message,
            'data'      => $this->data,
            'request'   => $this->requestdata,         
        ];
        return response()->json($data, $this->code);
    }
}
