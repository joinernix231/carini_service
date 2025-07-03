<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class ReadDeviceAPIRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

}
