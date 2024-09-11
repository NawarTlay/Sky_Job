<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Hash;
use Auth;


class AuthController extends Controller
{

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    protected function create(array $data, $role)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role
        ]);
    }

    public function registerCompany(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all(), 0);

        return response()->json([
            'error' => false,
            'data' => $user,
            'message' => 'تم إنشاء الحساب بنجاح',
        ], 201);
    }

    public function registerEmployee(Request $request)
    {

        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $this->create($request->all(), 1);

        return response()->json([
            'error' => false,
            'data' => $user,
            'message' => 'تم إنشاء الحساب بنجاح',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'error' => true,
                'message' => 'من فضلك أدخل كلمة المرور والبريد الالكتروني بشكل صحيح'
            ]);
        }

        $user = $request->user();
        $tokenResult = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'error' => false,
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'user' => $user,
            'message' => 'تم تسجيل الدخول بنجاح',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'error' => false,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }
}
