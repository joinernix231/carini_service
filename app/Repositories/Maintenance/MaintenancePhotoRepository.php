<?php

namespace App\Repositories\Maintenance;

use App\Models\Maintenance\MaintenancePhoto;
use App\Repositories\BaseRepository;

class MaintenancePhotoRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'maintenance_id',
        'client_device_id',
        'photo_type',
        'uploaded_at'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MaintenancePhoto::class;
    }

    /**
     * Crear múltiples fotos para un mantenimiento
     */
    public function createMultiplePhotos(int $maintenanceId, array $photosData): array
    {
        $createdPhotos = [];

        foreach ($photosData as $photoData) {
            $photoData['maintenance_id'] = $maintenanceId;

            $createdPhotos[] = parent::create($photoData);
        }

        return $createdPhotos;
    }

    /**
     * Obtener fotos por tipo
     */
    public function getPhotosByType(int $maintenanceId, string $photoType)
    {
        return $this->model
            ->where('maintenance_id', $maintenanceId)
            ->where('photo_type', $photoType)
            ->get();
    }

    /**
     * Obtener fotos por dispositivo del cliente
     */
    public function getPhotosByClientDevice(int $maintenanceId, int $clientDeviceId)
    {
        return $this->model
            ->where('maintenance_id', $maintenanceId)
            ->where('client_device_id', $clientDeviceId)
            ->get();
    }

    /**
     * Eliminar fotos de un mantenimiento
     */
    public function deletePhotosByMaintenance(int $maintenanceId): bool
    {
        return $this->model
            ->where('maintenance_id', $maintenanceId)
            ->delete() > 0;
    }
}
