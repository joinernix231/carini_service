<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateProfileAPIRequest;
use App\Models\User;
use App\Repositories\Client\ClientRepository;
use App\Repositories\Coordinator\CoordinatorRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Repositories\User\UserRepository;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;

class ProfileAPIController extends Controller
{
    public function __construct(
        private UserRepository $userRepository,
        private ClientRepository $clientRepository,
        private TechnicianRepository $technicianRepository,
        private CoordinatorRepository $coordinatorRepository
    ) {}

    public function update(UpdateProfileAPIRequest $request): JsonResponse
    {
        /**@var User $user*/
        $user = auth()->user();

        try {
            switch ($user->role) {
                case 'cliente':
                    $this->updateClientProfile($user, $request);
                    break;
                case 'tecnico':
                    $this->updateTechnicianProfile($user, $request);
                    break;
                case 'coordinador':
                    $this->updateCoordinatorProfile($user, $request);
                    break;
                default:
                    $this->updateUserProfile($user, $request);
                    break;
            }

            return ResponseUtil::makeResponse('Perfil actualizado correctamente.');

        } catch (\Exception $e) {
            return ResponseUtil::makeError('Error al actualizar el perfil: ' . $e->getMessage(), 500);
        }
    }

    private function updateClientProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $clientData = $request->only([
            'name', 'email', 'address', 'city', 'phone', 'document'
        ]);

        $this->clientRepository->updateProfile($user->id, $clientData);
    }

    private function updateTechnicianProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $userData = $request->only(['name', 'email']);
        $technicianData = $request->only(['document', 'phone', 'address']);

        $this->technicianRepository->updateFullProfile($user->id, $userData, $technicianData);
    }

    private function updateCoordinatorProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $userData = $request->only(['name', 'email']);
        $coordinatorData = $request->only(['identification', 'phone', 'address']);

        $this->coordinatorRepository->updateFullProfile($user->id, $userData, $coordinatorData);
    }

    private function updateUserProfile(User $user, UpdateProfileAPIRequest $request): void
    {
        $userData = $request->only(['name', 'email']);
        $this->userRepository->updateProfile($user->id, $userData);
    }
}
