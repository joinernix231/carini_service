<?php

namespace App\Http\Requests\Coordinator;

use App\Http\Requests\APIRequest;
use App\Models\Coordinator\Coordinator;
use Illuminate\Foundation\Http\FormRequest;

class CreateCoordinatorAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Coordinator::$rules;
    }
}
