<?php

namespace App\Http\Requests\Device;

use App\Http\Requests\APIRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class GetDeviceManualAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'expires' => 'nullable|integer'
        ];
    }

    public function messages(): array
    {
        return [
            'token.required' => 'Token de autenticación requerido',
            'token.string' => 'Token debe ser una cadena válida',
            'expires.integer' => 'Timestamp de expiración inválido'
        ];
    }
}
