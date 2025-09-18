<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginWithEmailAPIRequest;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;
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
                'policy_accepted' => !is_null($user->policy_accepted_at)
            ]
        ];

        return ResponseUtil::makeResponse('Login exitoso.', $data);
    }


    public function me(): JsonResponse
    {
        $user = auth()->user();

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'policy_accepted' => !is_null($user->policy_accepted_at)
        ];

        return ResponseUtil::makeResponse('Usuario obtenido correctamente.', $data);
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
}
