<?php

namespace App\Http\Requests\API\Other;

use App\Http\Requests\APIRequest;

class LoadImageAPIRequest extends APIRequest
{

    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required',
            // Sin "/", "\\" ni "..": evita escribir fuera de la carpeta en S3
            // (p. ej. sobrescribir PDFs de órdenes de servicio de otros clientes).
            'name' => ['required', 'string', 'max:255', 'not_regex:/(\\.\\.|\\/|\\\\)/']
        ];
    }

    public function messages(): array
    {
        $messages = parent::messages();
        $messages['file'] = 'Se debe enviar una imagen';
        return $messages;
    }
}
