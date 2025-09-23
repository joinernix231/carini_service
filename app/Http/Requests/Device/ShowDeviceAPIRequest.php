<?php

namespace App\Http\Requests\Device;

use Illuminate\Foundation\Http\FormRequest;

class ShowDeviceAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

}
