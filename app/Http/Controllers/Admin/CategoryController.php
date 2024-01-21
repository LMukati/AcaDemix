<?php 

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use App\Models\Category;
use Validator,DB,App;
use Auth,Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
class CategoryController extends Controller 
{
    public function __construct()
    {
        $this->category = new Category();
    }

    public function categoryListing(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $limit = 10;
        if(isset($input['limit'])){
            $limit = $input['limit'];
        }
        $query=$this->category->orderBy('created_at', 'DESC');
       
        $user=$query->paginate($limit);
       

        $this->data = $user;
        $this->status = true;
        $this->code = 200;
        $this->message ='Category get successfully.';
        return $this->jsonResponse();
    }

    public function categoryStore(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $rules =  [
            'name'=> 'required|regex:/^[a-zA-Z ]*$/|max:40',
            'type'=> 'required',
            
        ];                
        $message = [];
        $validator = Validator::make($input,$rules,$message);
        if ($validator->fails()) {
            $this->errorValidation($validator);
        } else {
            $data = new  Category;
            $data->name = $request->name;
            $data->type = $request->type;
            $data->save();
            if($data->id){
                $this->data = $data;
                $this->status = true;
                $this->code = 200;
                $this->message ='Category Added successfully.';
            }else{
                $this->status = false;
                $this->code = 200;
                $this->message ='Category Can`t Added successfully.';
            }
            
        }
        return $this->jsonResponse();
    }
    

    public function categoryUpdate(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $data = $this->category->findOrFail($request->id);
        $rules =  [
            'name'=> 'required|regex:/^[a-zA-Z ]*$/|max:40',
            'type'=> 'required',
            
        ];                
        $message = [];
        $validator = Validator::make($input,$rules,$message);
        if ($validator->fails()) {
            $this->errorValidation($validator);
        } else {
            $data->name = $request->name;
            $data->type = $request->type;
            $data->save();
            if($data->id){
                $this->data = $data;
                $this->status = true;
                $this->code = 200;
                $this->message ='Category updated successfully.';
            }else{
                $this->status = false;
                $this->code = 200;
                $this->message ='Category Can`t updated successfully.';
            }
        }
        return $this->jsonResponse();
    }
    
    public function categoryDelete(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $data = $this->category->findOrFail($request->id);
        
        if($data->id){
            $this->category->where('id',$data->id)->delete();
            $this->status = true;
            $this->code = 200;
            $this->message ='Category deleted successfully.';
        }else{
            $this->status = false;
            $this->code = 200;
            $this->message ='Category Can`t deleted successfully.';
        }
        return $this->jsonResponse();
    }
}   