<?php

namespace App\Utils\Criterias\Maintenance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;

class TechnicianMaintenancesCriteria implements CriteriaInterface
{
    protected int $technicianId;
    protected ?string $status;
    protected ?string $dateFrom;
    protected ?string $dateTo;

    public function __construct(
        int $technicianId,
        ?string $status = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->technicianId = $technicianId;
        $this->status = $status;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function apply($model, RepositoryInterface $repository): Builder|Model
    {
        $query = $model->where('technician_id', $this->technicianId);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->dateFrom) {
            $query->whereDate('date_maintenance', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date_maintenance', '<=', $this->dateTo);
        }

        return $query;
    }
}
