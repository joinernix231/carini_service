<?php

namespace App\Repositories\Maintenance;

use App\Events\Maintenance\MaintenanceCreated;
use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\BaseRepository;


class MaintenanceRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Maintenance::class;
    }

    public function create(array $input)
    {
        $input['status'] = 'pending';

        $maintenance = parent::create($input);

        event(new MaintenanceCreated($maintenance));

        return $maintenance;
    }

    public function assignTechnician(Maintenance $maintenance, Technician $technician, string $shift): Maintenance
    {
        $maintenance->technician_id = $technician->id;
        $maintenance->shift = $shift;
        $maintenance->status = 'assigned';
        $maintenance->save();

        return $maintenance;
    }

    public function assignMaintenance(Maintenance $maintenance, array $input): Maintenance
    {

        $maintenance->status = 'assigned';
        $maintenance->technician_id = $input['technician_id'];
        $maintenance->date_maintenance = $input['date_maintenance'];
        $maintenance->value = $input['value'];
        $maintenance->spare_parts = $input['spare_parts'];
        $maintenance->shift = $input['shift'];
        $maintenance->save();

        return $maintenance;
    }

}
