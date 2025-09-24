<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\AvailableTechnicianAPIRequest;
use App\Http\Resources\Technician\TechnicianResource;
use App\Repositories\Technician\TechnicianRepository;
use Illuminate\Http\JsonResponse;

class AvailableTechnicianAPIController extends Controller
{
    public function __construct(private readonly TechnicianRepository $technicianRepository)
    {}

    public function __invoke(AvailableTechnicianAPIRequest $request): JsonResponse
    {
        $date = $request->input('date');
        $shift = $request->input('shift');

        $availableTechnicians = $this->technicianRepository->getAvailableTechnicians($date, $shift);

        return $this->makeResponseResource(
            'Available technicians retrieved successfully',
            TechnicianResource::collection($availableTechnicians)
        );
    }
}
