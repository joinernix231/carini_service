<?php

namespace App\Http\Requests\API\Other;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\PreventsUploadOverwrite;

class LoadDocAPIRequest extends APIRequest
{
    use PreventsUploadOverwrite;

    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // PDF o imagen (el soporte de pago puede ser una captura de pantalla).
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,heic,heif', 'max:10240'],
            // Sin "/", "\\" ni "..": evita escribir fuera de la carpeta en S3
            // (p. ej. sobrescribir PDFs de órdenes de servicio de otros clientes).
            'name' => ['required', 'string', 'max:255', 'not_regex:#(\.\.|/|\\\\)#']
        ];
    }

    public function withValidator($validator): void
    {
        $this->rejectIfExists($validator, fn (string $name) => 'docs/pdfs/' . $name);
    }

    public function messages(): array
    {
        $messages = parent::messages();
        $messages['file'] = 'Se debe enviar un archivo';
        $messages['mimes'] = 'El archivo debe ser PDF o imagen (JPG, PNG, WEBP o HEIC)';
        $messages['not_regex'] = 'El nombre del archivo no es válido';
        return $messages;
    }
}
