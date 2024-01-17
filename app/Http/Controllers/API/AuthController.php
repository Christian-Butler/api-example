<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(),
            [
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);


            if ($validator->fails()) {
                // create the JSON that will be returned in the response
                return response()->json(
                [
                    'status' => "Error",
                    'message' => 'validation error',
                    'errors' =>$validator->errors()
                ], 422);
                // Have a look at all the Response codes in by ctrl click HTTP_UNPROCESSABLE_ENTITY below.
               
            }

            // If you get this far, validation passed, so create the user in the database.
            $user = User::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            $token = $user->createToken('api-example')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 201);
        }

        catch(\Throwable $th){
            return response()->json([
                'message' => 'Error, coudn\'t register user',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request){
        try{
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our records.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();
            $token = $user->createToken('api-example')->plainTextToken;

            return response()->json([
                'message' => 'User logged in successfully',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);

        }

        catch(\Throwable $th){
            return response()->json([
                'message' => 'Error, coudn\'t login user',
                'data' => $th->getMessage()
            ], 500);
        }
    }

    public function user(){
        return response()->json([
            'message' => 'User fetched successfully',
            'data' => [
                'user' => Auth::user()
            ]
        ], 200);
    }

    public function logout(){

        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
        // try{
           
        // }

        // catch(\Throwable $th){
        //     return response()->json([
        //         'message' => 'Error, coudn\'t logout user',
        //         'data' => $th->getMessage()
        //     ], 500);
        // }
    }
}
