<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ReadClientAPIRequest;
use App\Http\Requests\Coordinator\CreateCoordinatorAPIRequest;
use App\Http\Resources\Coordinator\CoordinatorResource;
use App\Repositories\Coordinator\CoordinatorRepository;


class CoordinatorController extends Controller
{
    public function __construct(private readonly CoordinatorRepository $coordinatorRepository)
    {}

    public function index(ReadCoordinatorAPIRequest)
    {

    }

    public function store(CreateCoordinatorAPIRequest $request)
    {
        $input = $request->validated();

        $Coordinator = $this->coordinatorRepository->create($input);

        return $this->makeResponseResource('Coordinator created Successfully', new CoordinatorResource($Coordinator));
    }
}
