<?php 

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ReportUsers;
use Validator,DB,App;
use Auth,Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\Admin\AdminLoginResource;
class UserController extends Controller 
{
    public function __construct()
    {
        $this->user = new User();
        $this->reportUsers =new ReportUsers();
    }

    public function login(Request $request)
    {
        $this->code = 200;
        $input =  $request->all();
        $this->requestdata = $input;
        $validator = Validator::make($input, [
            'email'            => 'required',
            'password'            => 'required',
            
        ]);
        if ($validator->fails()) {
            $this->errorValidation($validator);
        } else {
            $users = $this->user->where(['email'=>$request->email])->first(); 
            if($users){
                if(Auth::attempt(['email' => $users->email, 'password' => $request->password])){ 
                
                    $userData = $this->user->where(['id'=>Auth::user()->id])->first();
                    $userData->tokens->each(function($token, $key) {
                        $token->delete();
                    });

                    $this->user->where('id',Auth::user()->id)->update(['last_logged_in_at'=>Carbon::now()]);
                    $token = $userData->createToken('auth_token')->plainTextToken;
                    $userData['access_token']=$token; 
                    $this->message  ='Login successfully.';
                    $this->status   = true;
                    $this->code     = 200;
                    $this->data     =new AdminLoginResource($userData);
                }else{ 
                    $this->message  ='Users password incorrect.';
                    $this->status   = false;
                    $this->code     = 200;
                    $this->data     = (object)array(); 
                } 
            }else{
                $this->message  ='Users Not Found.';
                $this->status   = false;
                $this->code     = 200;
                $this->data     =(object)array(); 
            }
          
        }


        return $this->jsonResponse();
        
    }

    public function getProfile(Request $request)
    {
        $input = $request->all();
        $this->requestdata = $input;
        $this->data = new AdminResource($this->user->where(['id'=>Auth::user()->id])->first());
        $this->status = true;
        $this->code = 200;
        $this->message ='User get successfully.';
        return $this->jsonResponse();
    }


    public function storeAdmin(Request $request){
        $input = $request->all();
        $this->requestdata = $input;

        $rules =  [
            'name' => 'required',
            'email' => 'required|email|unique:users|max:190|regex:/(.+)@(.+)\.(.+)/i',
            'password'      => 'required|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',//max:15|
            'confirm_password'  => 'required|same:password',
        ];                
        $message = [];
        $validator = Validator::make($input, $rules, $message);
        if ($validator->fails()) {
            $this->errorValidation($validator);
        } else {


            $user  = new User;
            $user->name =  $request->name;
            $user->email =  $request->email;
            $user->user_type =  '0';
            $user->status =  '1';
            $user->password = Hash::make($request->password);
            $user->save();

            $this->data =  new AdminResource($user);
            $this->status = true;
            $this->code = 200;
            $this->message ='Admin added successfully.';
        }
       

        return $this->jsonResponse();
    }


    public function logout(Request $request){
        $this->code = 200;
        $input =  $request->all();
        $this->requestdata = $input;

        $this->user->where('id',Auth::user()->id)->update(['last_logged_out_at'=>Carbon::now()]);
        Auth::user()->tokens()->delete();
        $this->status = true;
        $this->message ="Log out successfully."; 
        return $this->jsonResponse(); 
    }


    public function userListing(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $limit = 10;
        if(isset($input['limit'])){
            $limit = $input['limit'];
        }
        $query=$this->user->where('user_type','1')->orderBy('created_at', 'DESC');
        if (isset($input['search']) && !empty( $input['search'])) {
             $search = $input['search'];
            $query->where(function($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }
        $user=$query->paginate($limit);
       

        $this->data = $user;
        $this->status = true;
        $this->code = 200;
        $this->message ='User get successfully.';
        return $this->jsonResponse();
    }


    public function reportUserListing(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $limit = 10;
        if(isset($input['limit'])){
            $limit = $input['limit'];
        }
        $query=$this->reportUsers->with(['user','reportUser'])->orderBy('created_at', 'DESC');
       
        $user=$query->paginate($limit);
       

        $this->data = $user;
        $this->status = true;
        $this->code = 200;
        $this->message ='Report User get successfully.';
        return $this->jsonResponse();
    }

    public function reportUserBlocked(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $validator = Validator::make($input, [
            'id'            => 'required',
        ]);
        if ($validator->fails()) {
            $this->errorValidation($validator);
        } else {
            $query=$this->reportUsers->where('id',$request->id)->first();
            if($query){
                if($query->status == '1'){
                    $this->user->where('id',$query->report_user_id)->update(['status'=>'1']);
                    $this->reportUsers->where('id',$query->id)->update(['status'=>'0']);
                    $this->status = true;
                    $this->code = 200;
                    $this->message ='Report user blocked successfully.';
                }else{
                    $this->user->where('id',$query->report_user_id)->update(['status'=>'2']);
                    $this->reportUsers->where('id',$query->id)->update(['status'=>'1']);
                    $this->status = true;
                    $this->code = 200;
                    $this->message ='Report user blocked successfully.';
                }
              
            }else{
                $this->status = false;
                $this->code = 200;
                $this->message ='Data not found.';
            }
           
        }
        return $this->jsonResponse();
    }

    public function userStatus(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $validator = Validator::make($input, [
            'id'=> 'required',
            'status'=> 'required',
        ]);
        if ($validator->fails()) {
            $this->errorValidation($validator);
        } else {
            $query=$this->user->where('id',$request->id)->first();
            if($query){
                $this->user->where('id',$query->id)->update(['status'=>$request->status]);
                $this->status = true;
                $this->code = 200;
                $this->message ='User status updated successfully.';
            }else{
                $this->status = false;
                $this->code = 200;
                $this->message ='Data not found.';
            }
           
        }
        return $this->jsonResponse();
    }

    public function dashboard(Request $request){
        $input = $request->all();
        $this->requestdata = $input;
        $fromMonth=date('m');
        $fromYear=date("Y");
        $totalUser  = $this->user->where('user_type','1')->count();
        $totalDating  = $this->user->where('user_type','1')->count();
        $totalConnect  = $this->user->where('user_type','1')->count();
        $totalBlock = $this->user->where('status','2')->count();
        $totalUserReported = $this->reportUsers->where('status','0')->count();
        $currentYearUserData = [];
        for($i = 1;$i <=12; $i++){
            $month = ($i) < 10 ? '0' + $i : $i;
            if($fromMonth >= $month){
                $count =  $this->user::whereYear('created_at', '=', $fromYear)
                                    ->whereMonth('created_at', '=', $month)
                                    ->count();
                array_push($currentYearUserData,[date("M", mktime(0, 0, 0,$month , 10))=>$count]);
            }
        }
        $data=['totalUser'=>$totalUser,'totalDating'=>$totalDating,'totalConnect'=>$totalConnect,'totalBlock'=>$totalBlock,'totalUserReported'=>$totalUserReported,'currentYearUserData'=>$currentYearUserData];
        $this->status = true;
        $this->code = 200;
        $this->data= $data;
        $this->message ='get dashboard data successfully.';
         
        return $this->jsonResponse();
    }
}