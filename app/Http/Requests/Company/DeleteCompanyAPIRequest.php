<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\APIRequest;

class DeleteCompanyAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }
}
