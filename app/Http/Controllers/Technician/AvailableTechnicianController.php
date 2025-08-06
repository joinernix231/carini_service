<?php

namespace App\Http\Controllers\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Technician\AvailableTechnicianAPIRequest;
use App\Http\Resources\Technician\TechnicianResource;
use App\Models\Technician\Technician;
use App\Repositories\Technician\TechnicianRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AvailableTechnicianController extends Controller
{
    public function __construct(private readonly TechnicianRepository $technicianRepository)
    {}

    public function __invoke(AvailableTechnicianAPIRequest $request): JsonResponse
    {
        $dates = $this->technicianRepository->getAvailableDates();

        return $this->makeResponse('Dates maintenances available retrieved successfully', $dates);
    }
}
