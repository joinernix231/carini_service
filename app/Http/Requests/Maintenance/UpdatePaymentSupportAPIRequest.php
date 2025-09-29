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

        return true;
    }

    public function rules(): array
    {
        return [
            'payment_support' => ['required', 'string'],
        ];
    }

}

