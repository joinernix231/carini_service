<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;

class MeController extends Controller
{
    public function me(): JsonResponse
    {
        /**@var User $user*/
        $user = auth()->user();

        // Datos básicos del usuario
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'policy_accepted' => !is_null($user->policy_accepted_at),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at
        ];

        // Agregar datos específicos según el rol
        switch ($user->role) {
            case 'cliente':
                $data['client_data'] = $this->getClientData($user);
                break;
            case 'tecnico':
                $data['technician_data'] = $this->getTechnicianData($user);
                break;
            case 'coordinador':
                $data['coordinator_data'] = $this->getCoordinatorData($user);
                break;
    
        }

        return ResponseUtil::makeResponse('Usuario obtenido correctamente.', $data);
    }

    private function getClientData($user): array
    {
        $client = $user->client;

        if (!$client) {
            return ['message' => 'No se encontraron datos del cliente'];
        }

        return [
            'identifier' => $client->identifier,
            'name' => $client->name,
            'legal_representative' => $client->legal_representative,
            'address' => $client->address,
            'city' => $client->city,
            'department' => $client->department,
            'email' => $client->email,
            'phone' => $client->phone,
            'status' => $client->status,
            'client_type' => $client->client_type,
            'document_type' => $client->document_type,
            'created_at' => $client->created_at,
            'updated_at' => $client->updated_at,
            'devices_count' => $client->clientDevices()->count(),
            'contacts_count' => $client->contacts()->count()
        ];
    }

    private function getTechnicianData($user): array
    {
        $technician = $user->technician;

        if (!$technician) {
            return ['message' => 'No se encontraron datos del técnico'];
        }

        return [
            'document' => $technician->document,
            'phone' => $technician->phone,
            'address' => $technician->address,
            'status' => $technician->status,
            'created_at' => $technician->created_at,
            'updated_at' => $technician->updated_at,
            'maintenances_count' => $technician->maintenances()->count(),
            'active_maintenances_count' => $technician->maintenances()->where('status', '!=', 'completed')->count()
        ];
    }

    private function getCoordinatorData($user): array
    {
        $coordinator = $user->coordinator;

        if (!$coordinator) {
            return ['message' => 'No se encontraron datos del coordinador'];
        }

        return [
            'identification' => $coordinator->identification,
            'address' => $coordinator->address,
            'phone' => $coordinator->phone,
            'status' => $coordinator->status,
            'created_at' => $coordinator->created_at,
            'updated_at' => $coordinator->updated_at
        ];
    }
}
