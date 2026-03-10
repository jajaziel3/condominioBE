<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:usuarios',
            'password' => ['required', 'confirmed', Password::min(8)],
            'telefono' => 'nullable|string|max:20',
            'id_departamento' => 'nullable|exists:departamentos,id_departamento',
        ]);

        try {
            $verificationToken = Str::random(64);

            $usuario = Usuario::create([
                'nombre' => $validated['nombre'],
                'apellido' => $validated['apellido'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telefono' => $validated['telefono'] ?? null,
                'id_departamento' => $validated['id_departamento'] ?? null,
                'rol' => 'residente',
                'activo' => true,
                'email_verification_token' => $verificationToken,
            ]);

            // Enviar email de verificación
            Mail::send(new VerifyEmailMail($usuario));

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente. Por favor, verifica tu correo electrónico.',
                'data' => [
                    'usuario' => [
                        'id_usuario' => $usuario->id_usuario,
                        'nombre' => $usuario->nombre,
                        'apellido' => $usuario->apellido,
                        'email' => $usuario->email,
                        'rol' => $usuario->rol,
                        'email_verified' => !is_null($usuario->email_verified_at),
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar email del usuario
     */
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $usuario = Usuario::where('email_verification_token', $validated['token'])->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Token de verificación inválido o expirado'
            ], 400);
        }

        if (!is_null($usuario->email_verified_at)) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electrónico ya ha sido verificado'
            ], 400);
        }

        // Actualizar usuario con email verificado
        $usuario->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Correo electrónico verificado exitosamente. Ahora puedes iniciar sesión.',
            'data' => [
                'usuario' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'email' => $usuario->email,
                    'email_verified' => true,
                ]
            ]
        ]);
    }

    /**
     * Reenviar correo de verificación
     */
    public function resendVerificationEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:usuarios',
        ]);

        $usuario = Usuario::where('email', $validated['email'])->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if (!is_null($usuario->email_verified_at)) {
            return response()->json([
                'success' => false,
                'message' => 'El correo electrónico ya ha sido verificado'
            ], 400);
        }

        // Generar nuevo token
        $verificationToken = Str::random(64);
        $usuario->update(['email_verification_token' => $verificationToken]);

        // Enviar email de verificación
        Mail::send(new VerifyEmailMail($usuario));

        return response()->json([
            'success' => true,
            'message' => 'Se ha reenviado el correo de verificación'
        ]);
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $validated['email'])->first();

        if (!$usuario || !Hash::check($validated['password'], $usuario->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        if (!$usuario->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario inactivo'
            ], 403);
        }

        // Verificar que el email haya sido verificado
        if (is_null($usuario->email_verified_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, verifica tu correo electrónico antes de iniciar sesión',
                'email_verified' => false,
            ], 422);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente',
            'data' => [
                'usuario' => [
                    'id_usuario' => $usuario->id_usuario,
                    'nombre' => $usuario->nombre,
                    'apellido' => $usuario->apellido,
                    'email' => $usuario->email,
                    'rol' => $usuario->rol,
                    'id_departamento' => $usuario->id_departamento,
                    'email_verified' => true,
                ],
                'token' => $token,
            ]
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Obtener usuario actual
     */
    public function getUser(Request $request)
    {
        $usuario = $request->user();
        return response()->json([
            'success' => true,
            'data' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
                'rol' => $usuario->rol,
                'id_departamento' => $usuario->id_departamento,
                'email_verified' => !is_null($usuario->email_verified_at),
            ]
        ]);
    }
}
