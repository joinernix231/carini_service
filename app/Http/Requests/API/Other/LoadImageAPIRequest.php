<?php

namespace App\Http\Requests\API\Other;

use App\Http\Requests\APIRequest;
use App\Http\Requests\Concerns\PreventsUploadOverwrite;

class LoadImageAPIRequest extends APIRequest
{
    use PreventsUploadOverwrite;

    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // La app siempre envía multipart (src/services/UploadImage.ts). Se valida
            // por contenido real: foto de cámara/galería (jpeg/heic/webp) o firma (png).
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:10240'],
            // Sin "\\" ni "..". Solo se permite "/" para las carpetas que usa la app
            // (maintenances/..., devices/...); evita escribir fuera de images/ en S3.
            'name' => [
                'required', 'string', 'max:255',
                'not_regex:#(\.\.|\\\\)#',
                'regex:#^((maintenances|devices)/)?[^/]+$#',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $this->rejectIfExists($validator, fn (string $name) => 'images/' . $name . '.PNG');
    }

    public function messages(): array
    {
        $messages = parent::messages();
        $messages['file'] = 'Se debe enviar una imagen';
        $messages['mimes'] = 'La imagen debe ser JPG, PNG, WEBP o HEIC';
        $messages['regex'] = 'El nombre del archivo no es válido';
        $messages['not_regex'] = 'El nombre del archivo no es válido';
        return $messages;
    }
}
