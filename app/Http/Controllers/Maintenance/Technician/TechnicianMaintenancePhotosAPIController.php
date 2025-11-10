<?php

namespace App\Http\Controllers\Maintenance\Technician;

use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\UploadMultiplePhotosRequest;
use App\Http\Resources\Maintenance\MaintenancePhotoResource;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Maintenance\MaintenancePhotoRepository;
use Illuminate\Http\JsonResponse;

class TechnicianMaintenancePhotosAPIController extends Controller
{
    private MaintenancePhotoRepository $maintenancePhotoRepository;

    public function __construct(MaintenancePhotoRepository $maintenancePhotoRepository)
    {
        $this->maintenancePhotoRepository = $maintenancePhotoRepository;
    }

    public function uploadPhoto(Maintenance $maintenance, UploadMultiplePhotosRequest $request): JsonResponse
    {
        $photosData = $request->validated()['photos'];

        // Agregar maintenance_id a cada foto
        foreach ($photosData as &$photoData) {
            $photoData['maintenance_id'] = $maintenance->id;
        }

        $createdPhotos = $this->maintenancePhotoRepository->createMultiplePhotos($maintenance->id, $photosData);

        return $this->makeResponseResource('Maintenance Photos uploaded successfully', MaintenancePhotoResource::collection($createdPhotos));
    }
}

