<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPushIds;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\TaskResource;
use App\Traits\EmailTrait;
use App\Http\Resources\Progress_listResource;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class UserApiController extends BaseController
{

    use EmailTrait;
    public function show($id)
    {
        $response = [
          'status' => true,
          'message' =>  'User retrieved successully',
          'data' => new UserResource(User::find($id))
        ];

        return response($response, 201);
    }

    public function store(Request $request)
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255'],
            'password' => ['required', 'min:6'], //need to pass password_confirmation also in request
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = new User();
        $user->first_name = $request->get('first_name');
        $user->last_name = $request->get('last_name');
        $user->email = $request->get('email');
        $user->phone = $request->get('phone');
        $user->password = Hash::make($request->get('password'));
        $user->save();

        //$token = $user->createToken('app-token')->plainTextToken;

        //$user->token = $token;
        $response = [
          'status' => true,
          'message' =>  'User registered successully',
          'data' => new UserResource($user)
        ];

        return response($response, 201);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'device_token' => 'nullable',
            'device_type' => 'nullable'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);       
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->sendError('Login Failed, Invalid Email Or Password!',null, 422);
        }

        if (!Hash::check($request->password, $user->password)) {
            return $this->sendError('Login Failed, Invalid Email Or Password!',null, 422);
        }

        $token = $user->createToken('app-token')->plainTextToken;
        $user->token = $token;

        if($request->device_token) 
            {
                $push = new UserPushIds();
                $push->push_id = $request->device_token ? $request->device_token : '';
                $push->device_type = $request->device_type ? $request->device_type : '';
                $push->user_id = auth()->check() ? auth()->user()->id : '';
                $push->save();
            }

        return $this->sendResponse($user,'User logged in successully');
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $response = [
          'status' => true,
          'message' =>  'User logged out successully',
        ];
        return response()->json($response, 200);
    }

    /*
    /* Consumer of this API will request for password reset link by providing email id registered.
    /* This reset link will be sent to the provided email id, if it exists.
    /* After clicking the password reset link in the email, user is redirected to web interface.
    /* And there user is able to reset the password using Laravel's default auth views.
    */
    public function forgotPassword(Request $request)
    {
        $rules = ['email' => "required|email",];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            try {
                $mail = Password::sendResetLink($request->only('email'));
                switch ($mail) {
                    case Password::RESET_LINK_SENT:
                        return response()->json(['errors' => 'Reset password link sent on your email id.'], 201);
                    case Password::INVALID_USER:
                        return response()->json(['errors' => 'We can\'t find a user with that email address.'], 404);
                }
            } catch (\Swift_TransportException $ex) {
                return response()->json(['errors' => $ex->getMessage(), 500]);
            } catch (Exception $ex) {
                return response()->json(['errors' => $ex->getMessage(), 500]);
            }
        }
    }

    public function forgetPassword(Request $request)
    {

        $rules = ['email' => "required",];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return $this->sendError([$validator->errors(), 'errors'],422);

        $user = User::where('email',$request->email)->first(); 

        if (!$user) {
            return $this->sendError('User does not exists!',null, 422);
        }

        $isCheck = PasswordReset::where('email',$request->email)->first(); 
        $otp = rand(1000,9999);

        if($isCheck){
            $isCheck->email = $request->email;
            $isCheck->token = $otp;
            $isCheck->save();
        }else{
            $passChange = new PasswordReset;
            $passChange->email = $request->email;
            $passChange->token = $otp;
            $passChange->save();
        }

        $this->sendMail(['email' => $request->email, 'password' => $request->password, 'subject' => "Forgot Password", 'token' => $otp], 'emails.forget-password');
       
        return $this->sendResponse(true,'OTP sent to Email successully');

    }

    public function verifyOtp(Request $request)
    {
        $rules = ['email' => "required|email",'otp' => "required",];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return $this->sendError([$validator->errors(), 'errors'],422);

        $isCheck = PasswordReset::where('email',$request->email)->where('token',$request->otp)->first(); 

        if (!$isCheck) {
            return $this->sendError("Invalid OTP",null,400);
        }

        return $this->sendResponse("OTP Verified Successfully!",null);
        
    }

    public function resetPassword(Request $request)
    {
        $rules = ['email' => "required|email" , "password"=> "required"];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return $this->sendError([$validator->errors(), 'errors'],422);
       
        $user = User::where('email',$request->email)->first(); 

        if (!$user) {
            return $this->sendError('User does not exists!',null, 422);
        }

        $user->password = Hash::make($request->get('password'));
        $user->save();

        return $this->sendResponse(null,"Password Reset Successfully!");

    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'email' => ['required|email'],
            'current_password' => ['required', 'min:6'],
            'password' => ['required', 'min:6'], //need to pass password_confirmation also in request
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(),null, 422);
        }

        if (!Hash::check($request->get('current_password'), $request->user()->password)) {
            return $this->sendError("The provided password does not match your current password.",null, 422);
        }

        $request->user()->forceFill([
            'password' => Hash::make($request->get('password')),
        ])->save();

        return $this->sendResponse(null,"Password Reset successfully.");
    }

    public function update(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
        ]);
   
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 500);
       
        }

        $user = User::find($id);
        $user->first_name = $user->first_name ? $request->first_name: $user->first_name; 
        $user->last_name = $user->last_name ? $request->last_name: $user->last_name; 
        $user->phone = $user->phone ? $request->phone: $user->phone; 
        $user->save(); 

        return response(['data' => 'Use Updated successfully.'], 201);



    }

    public function tasks($id)
    {
        $user = User::find($id);
        $tasks = $user->tasks()->with('user', 'images', 'progress_lists')->paginate();
        return TaskResource::collection($tasks);
    }

    public function progress_lists($id)
    {
        $user = User::find($id);
        $progress_lists = $user->progress_lists()->with('task')->paginate();
        return Progress_listResource::collection($progress_lists);
    }
}
