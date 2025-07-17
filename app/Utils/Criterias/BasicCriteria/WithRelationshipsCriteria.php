<?php

namespace App\Utils\Criterias\BasicCriteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Created by PhpStorm.
 * User: Juan Paz
 * Date: 8/05/2017
 * Time: 4:32 PM
 */
class WithRelationshipsCriteria implements CriteriaInterface
{
    private $arrayWith;

    //
    public function __construct($arrayWith)
    {

        $this->arrayWith = $arrayWith;
    }


    public function apply($model, RepositoryInterface $repository)
    {
        $model = $model->with($this->arrayWith);
        return $model;
    }
}
