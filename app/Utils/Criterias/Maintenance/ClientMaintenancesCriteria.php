<?php
/**
 * Created by PhpStorm.
 * User: Juan Paz
 * Date: 16/05/2017
 * Time: 2:36 PM
 */

namespace App\Utils\Criterias\Maintenance;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Contracts\CriteriaInterface;


class ClientMaintenancesCriteria implements CriteriaInterface
{
    protected int $clientId;

    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    public function apply($model, RepositoryInterface $repository): Builder|Model
    {
        return $model->whereHas('clientDevice', function ($query) {
            $query->where('client_id', $this->clientId);
        });
    }
}
