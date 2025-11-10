<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->data;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $data['title'] ?? '',
            'body' => $data['body'] ?? '',
            'data' => $data['data'] ?? [],
            'read_at' => $this->read_at ? $this->read_at->toISOString() : null,
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}

