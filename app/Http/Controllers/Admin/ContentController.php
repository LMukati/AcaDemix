<?php 

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use App\Models\Contents;
use Validator,DB,App;
use Auth,Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
class ContentController extends Controller 
{
    public function __construct()
    {
        $this->contents = new Contents();
    }

    public function getStaticPage(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $slug = $request->slug;
       
        $contents=$this->contents->where('slug',$slug)->first();
        if($contents){
            $this->data = $contents;
            $this->status = true;
            $this->code = 200;
            $this->message ='Static Page get successfully.';
        }else{
            $this->status = false;
            $this->code = 200;
            $this->message ='Not Found.';
            
        }
        return $this->jsonResponse();
    }


    public function staticPageUpdate(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $data = $this->contents->findOrFail($request->id);
        if($data){
            $rules =  [
                'description'=> 'required',
            ];                
            $message = [];
            $validator = Validator::make($input,$rules,$message);
            if ($validator->fails()) {
                $this->errorValidation($validator);
            } else {
                $data->description = $request->description;
                $data->save();
                if($data->id){
                    $this->data = $data;
                    $this->status = true;
                    $this->code = 200;
                    $this->message ='Static Page updated successfully.';
                }else{
                    $this->status = false;
                    $this->code = 200;
                    $this->message ='Static Page Can`t updated successfully.';
                }
            }
        }else{
            $this->status = false;
            $this->code = 200;
            $this->message ='Not Found.';
        }
        
        return $this->jsonResponse();
    }
}   