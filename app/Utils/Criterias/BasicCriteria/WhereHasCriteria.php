<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 22/04/16
 * Time: 03:37 PM
 */

namespace App\Utils\Criterias\BasicCriteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class WhereHasCriteria implements CriteriaInterface
{
    private $relation, $field, $value, $operator;

    //
    public function __construct($relation, $field = null, $value = null, $operator = "=")
    {
        $this->relation = $relation;
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;

        if ($this->operator == 'like') {
            $this->value = '%' . $this->value . '%';
        }
    }

    public function apply($model, RepositoryInterface $repository)
    {
        if (is_array($this->relation)) {
            foreach ($this->relation as $r) {
                $model = $model->wherehas($r);
            }
            return $model;
        } elseif (is_null($this->field))
            return $model->whereHas($this->relation);

        $model = $model->whereHas($this->relation, function ($q) {
            if ($this->operator == 'in') {
                $q->whereIn($this->field, $this->value);
            } else
                $q->where($this->field, $this->operator, $this->value);
        });
        return $model;
    }
}
