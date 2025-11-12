<?php

namespace App\Repositories\Maintenance;

use App\Models\Maintenance\MaintenanceServiceReport;
use App\Repositories\BaseRepository;

class MaintenanceServiceReportRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'maintenance_id',
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
     * @param int $maintenanceId
     * @return MaintenanceServiceReport
     */
    public function createOrUpdate(array $attributes, int $maintenanceId): MaintenanceServiceReport
    {
        $attributes['maintenance_id'] = $maintenanceId;
        
        return $this->model->updateOrCreate(
            ['maintenance_id' => $maintenanceId],
            $attributes
        );
    }
}

