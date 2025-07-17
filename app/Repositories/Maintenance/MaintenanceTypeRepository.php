<?php

namespace App\Repositories\Maintenance;

use App\Models\Maintenance\MaintenanceType;
use App\Repositories\BaseRepository;


class MaintenanceTypeRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return MaintenanceType::class;
    }

}
