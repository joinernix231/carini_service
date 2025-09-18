<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coordinator\InactiveCoordinator;
use App\Http\Resources\Coordinator\CoordinatorResource;
use App\Models\Coordinator\Coordinator;
use App\Repositories\Coordinator\CoordinatorRepository;
use Illuminate\Http\JsonResponse;

class InactiveCoordinatorAPIController extends Controller
{
    public function __construct(private readonly CoordinatorRepository $coordinatorRepository)
    {}


    public function __invoke(Coordinator $coordinator, InactiveCoordinator $request): JsonResponse
    {
        $input = $request->validated();

        $coordinator = $this->coordinatorRepository->update($input, $coordinator->id);

        return $this->makeResponseResource('Coordinator update status successfully', new CoordinatorResource($coordinator));
    }
}
