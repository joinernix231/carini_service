<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;

class ReadCompanyAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            //
        ];
    }
}
