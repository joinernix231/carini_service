<?php

namespace App\Http\Requests\API\Other;

use App\Http\Requests\APIRequest;

class LoadDocAPIRequest extends APIRequest
{

    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required',
            'name' => 'required|string'
        ];
    }

}
