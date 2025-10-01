<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\BaseJsonResource;
use App\Http\Resources\Client\ClientResource;
use App\Http\Resources\Coordinator\CoordinatorResource;
use App\Http\Resources\Technician\TechnicianResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class ProfileResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'policy_accepted' => !is_null($user->policy_accepted_at),
            'created_at' => $user->created_at?->toDateTimeString(),
            'updated_at' => $user->updated_at?->toDateTimeString(),
        ];

        // Agregar datos específicos según el rol
        switch ($user->role) {
            case 'cliente':
                if ($user->client) {
                    $data['client_data'] = new ClientResource($user->client);
                }
                break;
            case 'tecnico':
                if ($user->technician) {
                    $data['technician_data'] = new TechnicianResource($user->technician);
                }
                break;
            case 'coordinador':
                if ($user->coordinator) {
                    $data['coordinator_data'] = new CoordinatorResource($user->coordinator);
                }
                break;
        }

        return $data;
    }
}
