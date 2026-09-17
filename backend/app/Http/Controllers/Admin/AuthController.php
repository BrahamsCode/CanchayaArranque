<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo no tiene un formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $user = User::where('email', $datos['email'])->first();

        if (! $user || ! Hash::check($datos['password'], $user->password)) {
            // 422 con el mismo mensaje para email y contraseña: no delatamos cuál falló.
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no son correctas.',
            ]);
        }

        return response()->json([
            'token' => $user->createToken('admin')->plainTextToken,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada.']);
    }
}
