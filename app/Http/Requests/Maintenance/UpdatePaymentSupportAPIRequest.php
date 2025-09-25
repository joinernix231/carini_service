<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;

class UpdatePaymentSupportAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        /** @var Maintenance $maintenance */
        $maintenance = $this->route('maintenance');
        
        // Solo el cliente propietario del mantenimiento puede actualizar el soporte de pago
        return $maintenance && 
               $maintenance->clientDevice && 
               $maintenance->clientDevice->client_id === session('client_id');
    }

    public function rules(): array
    {
        return [
            'payment_support' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'payment_support.required' => 'El soporte de pago es obligatorio.',
            'payment_support.file' => 'El soporte de pago debe ser un archivo.',
            'payment_support.mimes' => 'El soporte de pago debe ser un archivo PDF, JPG, JPEG o PNG.',
            'payment_support.max' => 'El soporte de pago no puede ser mayor a 10MB.',
        ];
    }
}
