<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 23/02/16
 * Time: 11:30 AM
 */

namespace App\Utils\Criterias\BasicCriteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WhereFieldCriteria implements CriteriaInterface
{

    private $field, $value, $operator;

    //
    public function __construct($field, $value = null, $operator = "=")
    {
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        if($this->operator == 'in'){
            $model = $model->whereIn($this->field, $this->value);
            return $model;
        }
        $model = $model->where($this->field, $this->operator, $this->value);
        return $model;
    }
}
