<?php

namespace App\Http\Requests\Coordinator;

use App\Http\Requests\Concerns\AuthorizesRoles;
use Illuminate\Foundation\Http\FormRequest;

class ShowCoordinatorAPIRequest extends FormRequest
{
    use AuthorizesRoles;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->userHasRole('administrador', 'coordinador');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
