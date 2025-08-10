<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Registro de novo usuário
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:30|unique:users,username',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $avatarPath = null;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('a', 'public');
        }else{
            $avatarPath = 'i/avatar-default.png';
        }

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'avatar'   => $avatarPath,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => [
                'id'       => $user->id,
                'username' => $user->username,
            ],
            'token' => $token,
        ], 201);
    }

    // Login de usuário
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $login_type = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($login_type, $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email/Username or Password does not match.',
            ], 401);
        }

        $token = $user->createToken('LOGIN TOKEN')->plainTextToken;

        return response()->json([
            'user'  => [
                'id'       => $user->id,
                'username' => $user->username,
            ],
            'token' => $token,
        ], 200);
    }

    // Logout (revogar token atual)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    // Retorna informações do usuário autenticado
    public function user(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        if($user->bio == null){
            $user->bio = '';
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'        => $user->id,
                'name'      => $user->name,
                'username'  => $user->username,
                'bio'       => $user->bio,
                'avatar'    => $user->avatar_url,
                'created_at'=> $user->created_at->toIso8601String(),
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:30|unique:users,username,' . $user->id,
            'email'    => 'sometimes|string|email|unique:users,email,' . $user->id,
            'bio'      => 'nullable|string|max:500',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fields = ['name', 'username', 'email', 'bio'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $user->$field = $request->input($field);
            }
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('a', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso.',
            'data' => [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'bio'      => $user->bio,
                'avatar'   => $user->avatar_url,
            ],
        ]);
    }

    public function destroy(Request $request)
    {
        $user = Auth::user();
        $user->delete();

        return response()->json([
            'message' => 'Usuário excluído com sucesso.',
        ]);
    }
}
