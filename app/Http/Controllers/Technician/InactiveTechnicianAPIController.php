<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\InactiveTechnicianAPIRequest;
use App\Http\Resources\Technician\TechnicianResource;
use App\Models\Technician\Technician;
use App\Repositories\Technician\TechnicianRepository;
use Illuminate\Http\JsonResponse;

class InactiveTechnicianAPIController extends Controller
{
    public function __construct(private readonly TechnicianRepository $technicianRepository)
    {}


    public function __invoke(Technician $technical, InactiveTechnicianAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $technician = $this->technicianRepository->update($input, $technical->id);

        return $this->makeResponseResource('Technician update status successfully', new TechnicianResource($technician));
    }
}
