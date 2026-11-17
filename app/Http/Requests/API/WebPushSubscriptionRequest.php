<?php

namespace App\Http\Requests\API;

use App\Http\Requests\APIRequest;

class WebPushSubscriptionRequest extends APIRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cuerpo = PushSubscription.toJSON() del navegador, más content_encoding opcional.
     */
    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'url:https', 'max:500'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'content_encoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ];
    }
}
