<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Coordinator\Coordinator;
use App\Models\Technician\Technician;
use Illuminate\Validation\Rule;

class UpdateProfileAPIRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();

        $rules = [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', $this->uniqueRule('users', 'email', $user->id)],
            'phone' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
        ];

        switch ($user->role) {
            case 'cliente':
                $rules['document'] = ['sometimes', 'string', 'max:20'];
                $rules['department'] = ['sometimes', 'string', 'max:255'];
                $rules['legal_representative'] = ['sometimes', 'string', 'max:255'];
                $rules['client_type'] = ['sometimes', Rule::in(Client::CLIENT_TYPES)];
                $rules['document_type'] = ['sometimes', Rule::in(Client::DOCUMENT_TYPES)];
                break;
            case 'tecnico':
                $rules['document'] = ['sometimes', 'string', 'max:20'];
                break;
            case 'coordinador':
                $coordinatorId = $user->coordinator?->id;
                $rules['identification'] = ['sometimes', 'integer', $this->uniqueRule('coordinators', 'identification', $coordinatorId)];
                break;
        }

        return $rules;
    }
}
