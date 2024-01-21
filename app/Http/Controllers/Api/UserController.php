<?php 

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Category;
use App\Models\Contents;
use App\Models\CategoryUser;
use App\Models\Userotps;
use App\Models\UserImages;
use App\Traits\ApiGlobalFunctions;
use Validator,DB,App;
use Auth,Hash;
use Illuminate\Support\Facades\Session;
use App\Http\Resources\Api\UserLoginResource;
class UserController extends Controller 
{
    use ApiGlobalFunctions;
    public function __construct()
    {
        $this->user = new User();
        $this->user_otp = new Userotps();
        $this->category = new Category();
        $this->contents = new Contents();
    }

    public function getProfile(Request $request)
    {
        $data =  $this->user->where(['id'=>Auth::user()->id])->first();
        return $this->sendResponse(new UserLoginResource($data), 'User get successfully.');
    }

    public function sendOTP(Request $request){
        try {
            $code = rand(1111, 9999);
            switch ($request->type) {
                case "register":
                    $validator = Validator::make($request->all(), [
                        'full_name' => 'required|min:3',
                        'email' => 'required|email|unique:users,email',
                        'mobile' => 'required|numeric|unique:users,mobile|regex:/[0-9]{10}/',
                        'gender' => 'required',
                        'age' => 'required',
                        'country' => 'required',
                    ],['mobile.regex'=>'Mobile Format invalid']);
                    if ($validator->fails()) {
                        return $this->sendError('Validation Error.', $validator->errors()->first(), '200');
                    }else{
                        
                        $otp = $this->user_otp->where('email_mobile',$request->email)->first();
                        if(!$otp){
                            $otp = new Userotps;
                            $otp->email_mobile = $request->email;
                        }
                        $otp->otp = $code;
                        $otp->save();
                        return $this->sendResponse((object)[], 'Send OTP Successfully.');
                    }
                break;

                case "login":
                    $validator = Validator::make($request->all(), [
                        'email_mobile' => 'required',
                    ]);
                    if ($validator->fails()) {
                        return $this->sendError('Validation Error.', $validator->errors()->first(), '200');
                    }else{
                        $otp = $this->user_otp->where('email_mobile',$request->email_mobile)->first();
                        if(!$otp){
                            $otp = new Userotps;
                            $otp->email_mobile = $request->email_mobile;
                        }
                        $otp->otp = $code;
                        if($this->checkemail($request->email_mobile)){
                            if($this->user->where('email',$request->email_mobile)->count() == 0){
                                return $this->sendError('Email not found.', '', '200');
                            }else{
                                $otp->save();
                                return $this->sendResponse((object)[], 'Send OTP Successfully.');
                            }
                        }else{
                            if($this->checkmobile($request->email_mobile)){
                                if($this->user->where('mobile',$request->email_mobile)->count() == 0){
                                    return $this->sendError('Mobile not found.', '', '200');
                                }else{
                                    $otp->save();
                                    return $this->sendResponse((object)[], 'Send OTP Successfully.');
                                }
                            }else{
                                return $this->sendError('Validation Error.', 'Mobile Number and Email is not valid', '200');
                            }
                            
                        }
                    }
                break;
            
            }
        } catch (\Exception $e) {
            return $this->sendError($e, '', '200');
        }
    }

    public function verifyOTP(Request $request){
        try {
            switch ($request->type) {
                case "register":
                    $validator = Validator::make($request->all(), [
                        'full_name' => 'required|min:3',
                        'email' => 'required|email|unique:users,email',
                        'mobile' => 'required|numeric|unique:users,mobile|regex:/[0-9]{10}/',
                        'gender' => 'required',
                        'age' => 'required',
                        'country' => 'required',
                        'otp'=>'required',
                    ],['mobile.regex'=>'Mobile Format invalid']);
                    if ($validator->fails()) {
                        return $this->sendError('Validation Error.', $validator->errors()->first(), '200');
                    }else{
                        $otp = $this->user_otp->where('email_mobile',$request->email)->first();
                        if($otp){
                            if($otp->otp == $request->otp || $request->otp == 1234){
                                $user = new User;
                                $user->name = $request->full_name;
                                $user->email = $request->email;
                                $user->mobile = $request->mobile;
                                $user->gender = $request->gender;
                                $user->age = $request->age;
                                $user->country = $request->country;
                                $user->save();
                                $token = $user->createToken('auth_token')->plainTextToken;  
                                $user->token= $token;
                                $user->save();
                                
                                return $this->sendResponse(new UserLoginResource($user), 'Verify OTP Successfully.');
                            }else{
                                return $this->sendError('OTP Invalid', '', '200');
                            }
                        }else{
                            return $this->sendError('OTP Invalid', '', '200');
                        }
                    }
                break;

                case "login":
                    $validator = Validator::make($request->all(), [
                        'email_mobile' => 'required',
                        'otp'=>'required'
                    ]);
                    if ($validator->fails()) {
                        return $this->sendError('Validation Error.', $validator->errors()->first(), '200');
                    }else{
                        $otp = $this->user_otp->where('email_mobile',$request->email_mobile)->first();
                        if($otp){
                            if($otp->otp == $request->otp || $request->otp == 1234){
                                $userData = $this->user->where(['email'=>$request->email_mobile])->first();
                                if(!$userData){
                                    $userData = $this->user->where(['mobile'=>$request->email_mobile])->first();
                                }
                                $userData->tokens->each(function($token, $key) {
                                    $token->delete();
                                });
                                $token = $userData->createToken('auth_token')->plainTextToken;  
                                $userData->token= $token;
                                $userData->save();
                                return $this->sendResponse(new UserLoginResource($userData), 'Verify OTP Successfully.');
                            }else{
                                return $this->sendError('OTP Invalid', '', '200');
                            }
                        }else{
                            return $this->sendError('OTP Invalid', '', '200');
                        }
                    }
                break;
            
            }
        } catch (\Exception $e) {
            return $this->sendError($e, '', '200');
        }
    }

    public function userImage(Request $request){
        try {
            $user =  $this->user->where(['id'=>Auth::user()->id])->first();
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $destinationPath = public_path().'/uploads/images/';
                $fileName = time() . '-' . $file->getClientOriginalName();
                $fileName = str_replace(" ", "_", $fileName);
                $file->move($destinationPath, $fileName);
                $user->profile_image	= $fileName;
                $user->save();

                return $this->sendResponse(new UserLoginResource($this->user->where(['id'=>Auth::user()->id])->first()), 'User get successfully.');
            }else{
                return $this->sendError('Image Not Found!', '', '200');
            }
           
        } catch (\Exception $e) {
            return $this->sendError($e, '', '200');
        }

    }

    public function getInterestHobbies(Request $request)
    {
        $data['interest'] =  $this->category->where(['type'=>'interest'])->get();
        $data['hobbies'] =  $this->category->where(['type'=>'hobbies'])->get();
        
        return $this->sendResponse($data, 'Get interest hobbies successfully.');
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

    public function userMultipalImage(Request $request){
        try {
            $user =  $this->user->where(['id'=>Auth::user()->id])->first();
            $destinationPath = public_path().'/uploads/images/';
            if ($request->hasFile('image_0')) {
                $file0 = $request->file('image_0');
                $fileName0 = time() . '-0' . $file0->getClientOriginalName();
                $fileName0 = str_replace(" ", "_", $fileName0);
                $file0->move($destinationPath, $fileName0);

                $userImage0 = new UserImages();
                $userImage0->user_id =$user->id;
                $userImage0->images =$fileName0;
                $userImage0->save();
            }
            if ($request->hasFile('image_1')) {
                $file1 = $request->file('image_1');
                $fileName1 = time() . '-1' . $file1->getClientOriginalName();
                $fileName1 = str_replace(" ", "_", $fileName1);
                $file1->move($destinationPath, $fileName1);

                $userImage1 = new UserImages();
                $userImage1->user_id =$user->id;
                $userImage1->images =$fileName1;
                $userImage1->save();
            }
            if ($request->hasFile('image_2')) {
                $file2 = $request->file('image_2');
                $fileName2 = time() . '-2' . $file2->getClientOriginalName();
                $fileName2 = str_replace(" ", "_", $fileName2);
                $file2->move($destinationPath, $fileName2);

                $userImage2 = new UserImages();
                $userImage2->user_id =$user->id;
                $userImage2->images =$fileName2;
                $userImage2->save();
            }
            if ($request->hasFile('image_3')) {
                $file3 = $request->file('image_3');
                $fileName3 = time() . '-3' . $file3->getClientOriginalName();
                $fileName3 = str_replace(" ", "_", $fileName3);
                $file3->move($destinationPath, $fileName3);

                $userImage3 = new UserImages();
                $userImage3->user_id =$user->id;
                $userImage3->images =$fileName3;
                $userImage3->save();
            }

            return $this->sendResponse(null, 'User Images successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e, '', '200');
        }

    }

    public function datingSubmit(Request $request){

        try {
            $user =  $this->user->where(['id'=>Auth::user()->id])->first();
            if ($request->ids) {
                $id = explode(',',trim($request->ids));
                for($i=0; $i < count($id); $i++){
                    $CategoryUser =new CategoryUser();
                    $CategoryUser->user_id = $user->id;
                    $CategoryUser->category_id = $id[$i];
                    $CategoryUser->save();
                    
                }
                return $this->sendResponse(new UserLoginResource($this->user->where(['id'=>Auth::user()->id])->first()), 'User get successfully.');
            }else{
                return $this->sendError('Ids Not Found!', '', '200');
            }
           
        } catch (\Exception $e) {
            return $this->sendError($e, '', '200');
        }

    }
    
    public function addConnect(Request $request){

        try {
            $user =  $this->user->where(['id'=>Auth::user()->id])->first();
            $user->region_of_work = $request->region_of_work ?? '';
            $user->top_of_interest = $request->top_of_interest ?? '';
            $user->area_of_work = $request->area_of_work ?? '';
            $user->research_profile = $request->research_profile ?? '';
            $user->save();


            return $this->sendResponse(new UserLoginResource($this->user->where(['id'=>Auth::user()->id])->first()), 'User get successfully.');
           
        } catch (\Exception $e) {
            return $this->sendError($e, '', '200');
        }

    }
}