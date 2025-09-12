<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coordinator\ReadCoordinatorAPIRequest;
use App\Http\Requests\Coordinator\CreateCoordinatorAPIRequest;
use App\Http\Resources\Coordinator\CoordinatorResource;
use App\Models\Coordinator\Coordinator;
use App\Repositories\Coordinator\CoordinatorRepository;
#use App\Repositories\User\UserRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use Illuminate\Http\JsonResponse;




class CoordinatorAPIController extends Controller
{
    public function __construct(private readonly CoordinatorRepository $CoordinatorRepository,)
    {}

    public function index(ReadCoordinatorAPIRequest $request) :JsonResponse
    {
        $this->CoordinatorRepository->pushCriteria(new OrderByCriteria('identification', 'asc'));

        if ($request->has('filters'))
            $this->CoordinatorRepository->pushCriteria(new FiltersCriteria($request->get('filters')));

        $coordinators = $request->has('unpaginated') ?
            $this->CoordinatorRepository->all() :
            $this->CoordinatorRepository->paginate(10);

        $coordinators->load(['Coordinator']);

        return $this->makeResponseResource('Controllers retrieved Successfully', CoordinatorResource::collection($coordinators));
    }

    public function store(CreateCoordinatorAPIRequest $request) :JsonResponse
    {
        $input = $request->validated();

        $Coordinator = $this->CoordinatorRepository->create($input);

        return $this->makeResponseResource('Coordinator created Successfully', new CoordinatorResource($Coordinator));
    }
}
