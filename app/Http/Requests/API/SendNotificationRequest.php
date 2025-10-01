<?php

namespace App\Http\Requests\API;

use App\Http\Requests\APIRequest;

class SendNotificationRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userIds' => ['required', 'array', 'min:1'],
            'userIds.*' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:500'],
            'data' => ['sometimes', 'array']
        ];
    }
}
