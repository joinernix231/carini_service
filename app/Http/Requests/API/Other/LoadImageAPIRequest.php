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
            'name' => 'required|string'
        ];
    }

    public function messages(): array
    {
        $messages = parent::messages();
        $messages['file'] = 'Se debe enviar una imagen';
        return $messages;
    }
}
