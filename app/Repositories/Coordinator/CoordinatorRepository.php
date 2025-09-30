<?php

namespace App\Repositories\Coordinator;


use App\Models\Coordinator\Coordinator;
use App\Repositories\BaseRepository;


class CoordinatorRepository extends BaseRepository
{
    protected $fieldSearchable = [

    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Coordinator::class;
    }

    public function getCoordinatorByUserId(int $userId): ?Coordinator
    {
        return $this->model
            ->where('user_id', $userId)
            ->first();
    }

    public function updateProfile(int $userId, array $attributes): Coordinator
    {
        return \DB::transaction(function () use ($userId, $attributes) {
            $coordinator = $this->getCoordinatorByUserId($userId);
            
            if (!$coordinator) {
                throw new \Exception('No se encontró el perfil del coordinador');
            }

            return $this->update($attributes, $coordinator->id);
        });
    }

    public function updateFullProfile(int $userId, array $userAttributes, array $coordinatorAttributes): array
    {
        return \DB::transaction(function () use ($userId, $userAttributes, $coordinatorAttributes) {
            $results = [];
            
            // Actualizar datos del usuario si hay atributos
            if (!empty(array_filter($userAttributes))) {
                $userRepo = app(\App\Repositories\User\UserRepository::class);
                $results['user'] = $userRepo->updateProfile($userId, $userAttributes);
            }
            
            // Actualizar datos del coordinador si hay atributos
            if (!empty(array_filter($coordinatorAttributes))) {
                $results['coordinator'] = $this->updateProfile($userId, $coordinatorAttributes);
            }
            
            return $results;
        });
    }

}
