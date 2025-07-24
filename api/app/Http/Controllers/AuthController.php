<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Registro de novo usuário
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:30|unique:users,username',
            'email'    => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6',
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bio'      => 'nullable|string|max:500',
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
        }

        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'avatar'   => $avatarPath,
            'bio'      => $request->bio,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    // Login de usuário
    public function login(Request $request)
    {
        // Validação dos campos de entrada
        $validator = Validator::make($request->all(), [
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $login = $request->login;
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $login)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Usuário não encontrado.',
                'errors'  => [
                    'login' => ['Não encontramos um usuário com esse ' . ($field === 'email' ? 'e-mail' : 'nome de usuário') . '.'],
                ],
            ], 401);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Senha incorreta.',
                'errors'  => [
                    'password' => ['A senha fornecida está incorreta.'],
                ],
            ], 401);
        }

        // Gera o token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Oculta a senha do retorno
        $user->makeHidden(['password', 'remember_token']);

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
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
        return response()->json($request->user());
    }
}
