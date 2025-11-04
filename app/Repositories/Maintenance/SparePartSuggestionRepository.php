<?php

namespace App\Repositories\Maintenance;

use App\Events\Maintenance\SparePartSuggestionCreated;
use App\Models\Maintenance\SparePartSuggestion;
use App\Repositories\BaseRepository;

class SparePartSuggestionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'maintenance_id',
        'client_device_id',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SparePartSuggestion::class;
    }

    public function create(array $attributes)
    {
        $suggestion = parent::create($attributes);

        event(new SparePartSuggestionCreated($suggestion));

        return $suggestion;
    }

    /**
     * Obtener sugerencias de un mantenimiento específico
     */
    public function getSuggestionsByMaintenance(int $maintenanceId)
    {
        return $this->model
            ->forMaintenance($maintenanceId)
            ->with(['clientDevice.device', 'maintenance.technician.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
