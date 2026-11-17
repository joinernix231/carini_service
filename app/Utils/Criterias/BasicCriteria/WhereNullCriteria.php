<?php

namespace App\Utils\Criterias\BasicCriteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WhereNullCriteria implements CriteriaInterface
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return $model->whereNull($this->field);
    }
}

