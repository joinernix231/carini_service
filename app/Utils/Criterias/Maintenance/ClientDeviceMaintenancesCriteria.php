<?php

namespace App\Utils\Criterias\Maintenance;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class ClientDeviceMaintenancesCriteria implements CriteriaInterface
{
    protected int $clientDeviceId;

    public function __construct(int $clientDeviceId)
    {
        $this->clientDeviceId = $clientDeviceId;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return $model->whereHas('clientDevices', function ($query) {
            $query->where('client_device.id', $this->clientDeviceId);
        });
    }
}

