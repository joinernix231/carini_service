<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\AvailableTechnicianAPIRequest;
use App\Http\Requests\Technician\CreateTechnicianAPIRequest;
use App\Http\Requests\Technician\DeleteTechnicianAPIRequest;
use App\Http\Requests\Technician\ReadTechnicianAPIRequest;
use App\Http\Requests\Technician\ShowTechnicianAPIRequest;
use App\Http\Requests\Technician\UpdateTechnicianAPIRequest;
use App\Http\Resources\Technician\TechnicianResource;
use App\Models\Technician\Technician;
use App\Repositories\Technician\TechnicianRepository;
use App\Repositories\User\UserRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\JsonResponse;

class TechnicianAPIController extends Controller
{
    public function __construct(private readonly TechnicianRepository $technicianRepository, private readonly UserRepository $userRepository)
    {}


    public function index(ReadTechnicianAPIRequest $request): JsonResponse
    {
        if ($request->has('filters'))
            $this->technicianRepository->pushCriteria(new FiltersCriteria($request->get('filters')));

        $technician = $request->has('unpaginated') ?
            $this->technicianRepository->all() :
            $this->technicianRepository->paginate(10);

        $technician->load('user');

        return $this->makeResponseResource('Technicians retrieved Successfully', TechnicianResource::collection($technician));
    }

    public function store(CreateTechnicianAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $user = $this->userRepository->createTechnician($input);

        $technicianData = Arr::only($input, ['document','phone', 'address']);
        $technicianData['user_id'] = $user->id;

        $technician = $this->technicianRepository->create($technicianData);

        return $this->makeResponseResource('Technician Created Successfully', new TechnicianResource($technician));
    }
    public function show(Technician $technical, ShowTechnicianAPIRequest $request): JsonResponse
    {
        $technical->load('user');

        return $this->makeResponseResource('Technician retrieved Successfully', new TechnicianResource($technical));
    }

    public function update(Technician $technical, UpdateTechnicianAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $userData = Arr::only($input, ['email', 'name']);
        if (!empty($userData)) {
            $this->userRepository->update($userData, $technical->user_id);
        }

        $technical = $this->technicianRepository->update($input,$technical->id);

        $technical->load('user');

        return $this->makeResponseResource('Technician Updated Successfully', new TechnicianResource($technical));
    }

    public function destroy(Technician $technical, DeleteTechnicianAPIRequest $request): JsonResponse
    {
        $this->technicianRepository->delete($technical->id);
        $this->userRepository->delete($technical->user_id);

        return $this->makeResponse('Technician Deleted Successfully',[$technical->id]);
    }
}
