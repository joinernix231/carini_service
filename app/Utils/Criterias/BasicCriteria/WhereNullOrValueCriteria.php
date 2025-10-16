<?php

namespace App\Utils\Criterias\BasicCriteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WhereNullOrValueCriteria implements CriteriaInterface
{
    private $field;
    private $value;

    public function __construct(string $field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where(function ($q) {
            $q->whereNull($this->field)
                ->orWhere($this->field, $this->value);
        });
    }
}
