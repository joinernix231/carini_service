<?php

namespace App\Http\Requests\Coordinator;

use App\Http\Requests\APIRequest;


class UpdateCoordinatorAPIRequest extends APIRequest
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
        $coordinatorId = $this->route('coordinator');

        $rules['identification'] = ['sometimes', 'string', $this->uniqueRule('coordinators', 'identification', $coordinatorId)];
        $rules['address'] = ['sometimes', 'string', 'max:255'];
        $rules['phone'] = ['sometimes', 'string', 'max:20'];
        $rules['email'] = ['sometimes', 'email', 'max:255', $this->uniqueRule('users', 'email', $coordinatorId->user_id)];

        return $rules;


    }
}
