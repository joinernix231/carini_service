<?php

namespace App\Repositories\Maintenance;

use App\Models\Maintenance\MaintenanceClientDevice;
use App\Models\Maintenance\MaintenanceServiceReport;
use App\Repositories\BaseRepository;

class MaintenanceServiceReportRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'maintenance_client_device_id',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MaintenanceServiceReport::class;
    }

    /**
     * Crear o actualizar el reporte de servicio
     *
     * @param array $attributes
     * @param int $maintenanceClientDeviceId
     * @return MaintenanceServiceReport
     */
    public function createOrUpdate(array $attributes, int $maintenanceClientDeviceId): MaintenanceServiceReport
    {
        // Obtener el MaintenanceClientDevice para sacar el maintenance_id
        $maintenanceClientDevice = MaintenanceClientDevice::findOrFail($maintenanceClientDeviceId);
        
        $attributes['maintenance_client_device_id'] = $maintenanceClientDeviceId;
        $attributes['maintenance_id'] = $maintenanceClientDevice->maintenance_id;
        
        return $this->model->updateOrCreate(
            ['maintenance_client_device_id' => $maintenanceClientDeviceId],
            $attributes
        );
    }
}

