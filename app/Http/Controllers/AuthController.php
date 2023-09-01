<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
            'npp' => 'required|numeric|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'message' => 'Something went wrong!',
                    'status' => 'error',
                    'code' => 400,
                ],
                'data' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'id' => $request->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'npp' => $request->npp,
            'npp_supervisor' => $request->npp_supervisor,
        ]);

        return response()->json([
            'meta' => [
                'message' => 'Register successful!',
                'status' => 'success',
                'code' => 201,
            ],
            'data' => $user
        ], 201);
    }


    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'message' => 'Something went wrong!',
                    'status' => 'error',
                    'code' => 400,
                ],
                'data' => $validator->errors()
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials!',
                'status' => 'error',
                'code' => 401,
            ],401);
        }

        $token = $user->createToken('authToken')->plainTextToken;
        return response()->json([
            'meta' => [
                'message' => 'Authenticated!',
                'status' => 'success',
                'code' => 200,
            ],
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]
        ], 201);
    }

}
