<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileAPIRequest;
use App\Http\Resources\Auth\ProfileResource;
use App\Models\User;
use App\Repositories\Client\ClientRepository;
use App\Repositories\Coordinator\CoordinatorRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Repositories\User\UserRepository;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Throwable;

class ProfileAPIController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ClientRepository $clientRepository,
        private readonly TechnicianRepository $technicianRepository,
        private readonly CoordinatorRepository $coordinatorRepository
    ) {}

    public function update(UpdateProfileAPIRequest $request): JsonResponse
    {
        /** @var User $user */
        $user  = auth()->user();
        $input = $request->validated();

        match ($user->role) {
            'cliente'     => $this->updateClient($user, $input),
            'tecnico'     => $this->updateTechnician($user, $input),
            'coordinador' => $this->updateCoordinator($user, $input),
            default       => null,
        };

            // Actualizar campos comunes del usuario (name, email) si vienen
            $userData = Arr::only($input, ['name', 'email']);
            if (!empty(array_filter($userData))) {
               $user = $this->userRepository->update(array_filter($userData), $user->id);
            }

            $user->load(['client', 'technician', 'coordinator']);

            return $this->makeResponseResource('Perfil actualizado correctamente.', new ProfileResource($user));
    }

    private function updateClient(User $user, array $input): void
    {
        // Aceptar 'document' como alias de 'identifier'
        if (isset($input['document']) && !isset($input['identifier'])) {
            $input['identifier'] = $input['document'];
        }

        $clientData = Arr::only($input, [
            'identifier', 'name', 'address', 'city', 'department', 'phone',
            'client_type', 'document_type', 'legal_representative', 'contacts'
        ]);

        if (empty(array_filter($clientData))) {
            return;
        }

        // Si existen contactos y el repositorio soporta updateWithContacts, usarlo
        if (!empty($clientData['contacts']) && method_exists($this->clientRepository, 'updateWithContacts')) {
            $client = $user->client ?? $this->clientRepository->findByUserId($user->id);
            $this->clientRepository->updateWithContacts($client->id, $clientData);
            return;
        }

        // Actualizar cliente directamente
        $client = $user->client;
        if ($client) {
            $this->clientRepository->update($clientData, $client->id);
        }
    }

    private function updateTechnician(User $user, array $input): void
    {
        // Normalizar nombres: permitir tanto 'document' como 'identification'
        if (isset($input['document']) && !isset($input['identification'])) {
            $input['identification'] = $input['document'];
        }

        $technicianData = Arr::only($input, ['identification', 'document', 'phone', 'address', 'specialties', 'experience']);

        if (empty(array_filter($technicianData))) {
            return;
        }

        $technician = $user->technician;
        if ($technician) {
            $this->technicianRepository->update($technicianData, $technician->id);
        }
    }

    private function updateCoordinator(User $user, array $input): void
    {
        if (isset($input['document']) && !isset($input['identification'])) {
            $input['identification'] = $input['document'];
        }

        $coordinatorData = Arr::only($input, ['identification', 'phone', 'address', 'department']);

        if (empty(array_filter($coordinatorData))) {
            return;
        }

        $coordinator = $user->coordinator;
        if ($coordinator) {
            $this->coordinatorRepository->update($coordinatorData, $coordinator->id);
        }
    }
}
