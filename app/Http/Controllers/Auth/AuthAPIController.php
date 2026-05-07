<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginWithEmailAPIRequest;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthAPIController extends Controller
{
    public function login(LoginWithEmailAPIRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return ResponseUtil::makeError('Credenciales inválidas', 401);
            }
        } catch (JWTException $e) {
            Log::error('JWTAuth::attempt failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return ResponseUtil::makeError('No se pudo crear el token', 500);
        }

        $user = JWTAuth::user();

        if (
            ($user->technician && $user->technician->status === 'inactive') ||
            ($user->coordinator && $user->coordinator->status === 'inactive') ||
            ($user->client && $user->client->status === 'inactive')
        ) {
            return ResponseUtil::makeError('Tu cuenta está inactiva, contacta con el administrador.', 403);
        }

        $data = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'policy_accepted' => !is_null($user->policy_accepted_at),
                'must_change_password' => $user->must_change_password
            ]
        ];

        return ResponseUtil::makeResponse('Login exitoso.', $data);
    }



    public function acceptPolicy(): JsonResponse
    {
        $user = auth()->user();

        if ($user->policy_accepted_at) {
            return ResponseUtil::makeResponse('Ya aceptaste las políticas.');
        }

        $user->update([
            'policy_accepted_at' => now()
        ]);

        return ResponseUtil::makeResponse('Políticas aceptadas con éxito.');
    }

    /**
     * Cambiar contraseña del usuario autenticado
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = auth()->user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return ResponseUtil::makeError('La contraseña actual es incorrecta', 422);
        }

        // Verificar que la nueva contraseña sea diferente a la actual
        if (Hash::check($request->new_password, $user->password)) {
            return ResponseUtil::makeError('La nueva contraseña debe ser diferente a la actual', 422);
        }

        // Actualizar contraseña y marcar que ya no debe cambiarla
        $user->update([
            'password' => Hash::make($request->new_password),
            'must_change_password' => false
        ]);

        return ResponseUtil::makeResponse('Contraseña cambiada exitosamente');
    }
}
