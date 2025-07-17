<?php
/**
 * Created by PhpStorm.
 * User: Sebastián
 * Date: 24/03/20
 * Time: 11:18 AM
 */

namespace App\Utils\Criterias\BasicCriteria;


use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class OrderByCriteria implements CriteriaInterface
{
    private $field, $operator;


    public function __construct($field, $operator = "desc")
    {
        $this->field = $field;
        $this->operator = $operator;
    }

    public function apply($model, RepositoryInterface $repository)
    {
        $this->model = $model;

        $relations = $model->getEagerLoads();
        $this->searchableFields = $repository->getFieldsSearchable();
        $this->makeOrder($relations);

        return $this->model;
    }

    private function makeOrder($relations)
    {
        $fieldArray = explode('.', $this->field);
        $fieldName = array_pop($fieldArray);
        $relation = implode('.', $fieldArray);
        if (empty($relation)) {
            if ($this->validateIfCanOrderByField($this->field))
                $this->model = $this->model->orderBy($this->field, $this->operator);
        } elseif (array_key_exists($relation, $relations)) {

            $tableName = $this->model->getModel()->{$relation}()->getModel()->getTable();

            $this->model->orderBy(function ($q) use ($fieldName, $tableName, $relation) {
                $q->select($fieldName)
                    ->from($tableName)
                    ->whereColumn($relation . '_id', $tableName . '.id');
            }, $this->operator);

        }
    }

    private function validateIfCanOrderByField($field)
    {
        return in_array($field, $this->searchableFields);
    }
}
