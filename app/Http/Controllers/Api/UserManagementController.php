<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            if ($request->input('pageNo'))
            {
                $page = $request->input('pageNo');
            }
            else{
                $page = 1;
            }
            if($request->input('numPerPage'))
            {
                $limit = $request->input('numPerPage');
            }
            else {
                $limit = 10;
            }

            $users = User::orderBy('created_at', 'desc');

            $totalCount = $users->count();

            $page_users = $users->skip(($page - 1) * $limit)->take($limit)->get();

            if ($totalCount == 0 ){
                return response()->json(['totalCount' => $totalCount, 'users' => []], 200);
            } else {
                return response()->json(['totalCount' => $totalCount, 'users' => $page_users],200);
            }

        } catch (JWTException $e) {
            return response()->json(['error' => 'User is not Logged in or Token expired'],301);
        }
    }

    public function getUser(Request $request)
    {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            $user_id = $request->input('userID');
            $user = User::find($user_id);
            return response()->json(['notification' => 'User get successfully!', 'user' => $user],200);
        }catch (JWTException $e) {
            return response()->json(['error' => 'User is not Logged in or Token expired'],301);
        }
    }

    public function saveUser(Request $request)
    {
        try {
            $current_user = JWTAuth::toUser(JWTAuth::parseToken());
            if($request->input('userId'))
            {
                $validator = Validator::make($request->all(), [
                    'email' => 'required|string|email|max:255',
                    'name' => 'required',
                    'phone' => 'required',
                ]);
            }
            else {
                $validator = Validator::make($request->all(),[
                    'email' => 'required|string|email|max:255|unique:users',
                    'name' => 'required',
                    'password' => 'required|min:6',
                    'phone' => 'required',
                ]);
            }

            if ($validator->fails()){
                return response()->json(['error' => $validator->errors()]);
            }

            try {
                if ($request->input('userId')) {
                    $user = User::find($request->input('userId'));
                }
                else {
                    $user = new User();
                }

                $user->name = $request->input('name');
                $user->email = $request->input('email');
                if ($request->input('password'))
                {
                    $user->password = Hash::make($request->input('password'));
                }
                $user->phone = $request->input('phone');
                $user->save();

                return response()->json(['notification' => 'User saved successfully!', 'user' => $user], 200);
            } catch (JWTException $e) {
                return response()->json(['error' => 'This email is already registered'], 401);
            }

        } catch (JWTException $e) {
            return response()->json(['error' => 'User is not Logged in or Token expired'],301);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $user_id = $request->get('userId');

            $user = User::find($user_id);
            if ($user)
            {
                $user->delete();
                return response()->json(['notification' => 'User deleted successfully!', 'user_id' => $user_id],200);
            } else {
                return response()->json(['error' => 'User don\t exist!', 'user_id' => $user_id],404);
            }
        } catch (JWTException $e) {
            return response()->json(['error'=>'User is not Logged in or Token expired'], 301);
        }

    }
}
