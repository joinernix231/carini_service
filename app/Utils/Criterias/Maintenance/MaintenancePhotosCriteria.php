<?php

namespace App\Utils\Criterias\Maintenance;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class MaintenancePhotosCriteria implements CriteriaInterface
{
    protected int $maintenanceId;

    public function __construct(int $maintenanceId)
    {
        $this->maintenanceId = $maintenanceId;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where('maintenance_id', $this->maintenanceId);
    }
}

