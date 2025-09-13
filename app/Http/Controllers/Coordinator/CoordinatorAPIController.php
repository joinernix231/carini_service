<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Coordinator\DeleteCoordinatorAPIRequest;
use App\Http\Requests\Coordinator\ReadCoordinatorAPIRequest;
use App\Http\Requests\Coordinator\CreateCoordinatorAPIRequest;
use App\Http\Requests\Coordinator\ShowCoordinatorAPIRequest;
use App\Http\Requests\Coordinator\UpdateCoordinatorAPIRequest;
use App\Http\Resources\Coordinator\CoordinatorResource;
use App\Models\Coordinator\Coordinator;
use App\Repositories\Coordinator\CoordinatorRepository;
use App\Repositories\User\UserRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;


class CoordinatorAPIController extends Controller
{

    public function __construct(private readonly UserRepository $userRepository, private readonly CoordinatorRepository $coordinatorRepository)
    {}

    public function index(ReadCoordinatorAPIRequest $request) :JsonResponse
    {
        $this->coordinatorRepository->pushCriteria(new OrderByCriteria('identification', 'asc'));

        if ($request->has('filters'))
            $this->coordinatorRepository->pushCriteria(new FiltersCriteria($request->get('filters')));

        $coordinators = $request->has('unpaginated') ?
            $this->coordinatorRepository->all() :
            $this->coordinatorRepository->paginate(10);

        $coordinators->load(['user']);

        return $this->makeResponseResource('Controllers retrieved Successfully', CoordinatorResource::collection($coordinators));
    }

    public function store(CreateCoordinatorAPIRequest $request) :JsonResponse
    {
        $input = $request->validated();

        $user = $this->userRepository->createCoordinator($input);

        $coordinatorData = Arr::only($input, ['identification', 'address', 'phone']);


        $coordinatorData['user_id'] = $user->id;

        $coordinator = $this->coordinatorRepository->create($coordinatorData);

        return $this->makeResponseResource('Coordinator created Successfully', new CoordinatorResource($coordinator));
    }

    public function show(Coordinator $coordinator, ShowCoordinatorAPIRequest $request): JsonResponse
    {
        $coordinator->load(['user']);
        return $this->makeResponseResource('Coordinators retrieved Successfully', new CoordinatorResource($coordinator));
    }


    public function update(Coordinator $coordinator, UpdateCoordinatorAPIRequest $request): JsonResponse
    {
        $input = $request->validated();


        $userData = Arr::only($input, ['email', 'name']);
        if (!empty($userData)) {
            $this->userRepository->update($userData, $coordinator->user_id);
        }

        $coordinatorData = Arr::only($input, ['identification', 'address', 'phone']);

        $coordinator =  $this->coordinatorRepository->update($coordinatorData, $coordinator->id);


        $coordinator->load(['user']);

        return $this->makeResponseResource('Coordinators updated Successfully', new CoordinatorResource($coordinator));
    }

    public function destroy(DeleteCoordinatorAPIRequest $request, int $id)
    {
        $this->userRepository->delete($id);
        $this->coordinatorRepository->delete($id);

        return $this->makeResponse('Client deleted Successfully', [$id]);
    }
}
