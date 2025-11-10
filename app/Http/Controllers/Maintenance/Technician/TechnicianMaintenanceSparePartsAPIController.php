<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\SuggestSparePartRequest;
use App\Http\Resources\Maintenance\SparePartSuggestionResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\SparePartSuggestionRepository;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenanceSparePartsAPIController extends Controller
{
    private SparePartSuggestionRepository $sparePartSuggestionRepository;

    public function __construct(SparePartSuggestionRepository $sparePartSuggestionRepository)
    {
        $this->sparePartSuggestionRepository = $sparePartSuggestionRepository;
    }

    /**
     * Sugerir cambio de repuesto para un mantenimiento
     *
     * @param Maintenance $maintenance
     * @param SuggestSparePartRequest $request
     * @return JsonResponse
     */
    public function suggestSparePart(Maintenance $maintenance, SuggestSparePartRequest $request): JsonResponse
    {
        $input = $request->validated();

        // Crear sugerencia
        $suggestion = $this->sparePartSuggestionRepository->create([
            'maintenance_id' => $maintenance->id,
            'client_device_id' => $input['client_device_id'],
            'description' => $input['description'],
            'photo' => $input['photo'] ?? null,
        ]);

        // Cargar relaciones para el resource
        $suggestion->load(['clientDevice.device']);

        return $this->makeResponseResource(
            'Sugerencia de cambio de repuesto registrada exitosamente',
            new SparePartSuggestionResource($suggestion)
        );
    }
}

