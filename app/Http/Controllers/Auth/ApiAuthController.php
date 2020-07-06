<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Mail\VerificationMail;
use App\Models\UserActivation;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class ApiAuthController extends Controller
{
    //

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required|min:6'
        ]);

        if($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }


        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid email or password!'], 401);
            }
        } catch (JWTException $e) {
            Log::info('Error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Couldn\'t create token'], 500);
        }

        if (Auth::validate($credentials))
        {
            $user = Auth::getUser();
        }

        if (!$user->email_verified_at) {
            return response()->json(['error'=>'Please verify your email address!'], 401);
        }

        return response()->json(['token' => $token, 'user' => $user], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:190',
            'email' => 'required|email|string|max:190|unique:users',
            'phone' => 'required|string',
            'password' => 'required|min:6',
        ]);

        if($validator->failed())
        {
            return response()->json($validator->errors());
        }

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->phone  = $request->input('phone');
        $user->password = Hash::make($request->input('password'));
        $user->save();

        $link_code = Str::random('60');
        $user_activation = new UserActivation();
        $user_activation->user_id = $user->id;
        $user_activation->token = $link_code;
        $user_activation->save();

//        try {
//            Mail::to($user->email)->send(new VerificationMail($link_code));
//        } catch (\Exception $e){
//            Log::info('Verification Email Error!: ', ['error' => $e->getMessage()] );
//        }

        return response()->json(['notification'=> "We have sent you an email to verify your registration. Please click the link in the email. The email may take some time to get to you and may end up in your spam folder"],200);

    }

    public function userActivation(Request $request)
    {
        $token = $request->input('token');
        if (empty($token)) {
            return response()->json(['error'=>'Token is not exist'],404);
        }
        $user_activation = UserActivation::where('token', '=', $token)->first();

        if($user_activation){
            $user = User::find($user_activation->user_id);
            $user->email_verified_at = Carbon::now();
            $user->save();
            $user_activation->delete();
            return response()->json(['notification' => 'Email verified successfully!'],200);
        }  else {
            return response()->json(['error'=>'Email veryfied failed!. Please verify your email address again!'],404);
        }
    }

    public function forgotPassword(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', '=', $email)->first();
        if (empty($user) || count($user) ==  0) {
            return response()->json(['data'=>'Email doesn\'t found on our database'], 404);
        }
        $this->send($request->email);
        return response()->json(['data'=>'Reset Email is sent successfully, Please check your inbox'], 200);
    }

    public function send($email)
    {
        $token = $this->createToken($email);
//        Mail::to($email)->send(new ResetPasswordMail($token, $email));
    }

    public function createToken($email) {
        $model = DB::table('password_resets')->where('email', $email)->first();
        if($model){
            return $model->token;
        }
        $token = Str::random(60);
        $this->saveToken($token, $email);
        return $token;
    }

    public function saveToken($token, $email) {
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
    }

    public function resetPassword(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()],401);
        }

        $pass_check = DB::table('password_resets')->where('email', '=', $request->input('email'))
            ->where('token', '=', $request->input('token'))->first();

        if ($pass_check) {
            $user = User::where('email', '=', $request->email)->first();
            $user->update(['password'=>bcrypt($request->password)]);

            return response()->json(['notification'=>'Password Successfully Changed'], 201);
        }
        else {
            return response()->json(['error'=>'Token or Email is not correct'], 404);
        }

    }



}
