<?php
/**
 * Created by PhpStorm.
 * User: Juan Paz
 * Date: 16/05/2017
 * Time: 2:36 PM
 */

namespace App\Utils\Criterias\BasicCriteria;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class FiltersCriteria implements CriteriaInterface
{

    private $filters, $model, $searchableFields;

    //
    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $this->model = $model;

        $relations = $model->getEagerLoads();
        $this->searchableFields = $repository->getFieldsSearchable();

        $filters = $this->getFilters();
        $this->makeFilter($filters, $relations);

        return $this->model;
    }

    private function getFilters()
    {
        $f = explode(';', $this->filters);
        $filters = array();
        foreach ($f AS $filter) {
            if (!empty($filter))
                $filters[] = $filter;
        }
        return $filters;
    }

    private function makeFilter($filters, $relations)
    {
        foreach ($filters AS $filter) {
            $parts = explode('|', $filter);
            list($field, $comparator, $value) = $parts;
            $fieldArray = explode('.', $field);
            $fieldName = array_pop($fieldArray);
            $relation = implode('.', $fieldArray);

            if (empty($relation)) {
                if ($this->validateIfCanFilterByField($field)) {
                    $this->filterByField($field, $comparator, $value);
                }
            } elseif (array_key_exists($relation, $relations)) {
                $this->model = $this->model->whereHas($relation, function ($q) use ($fieldName, $comparator, $value) {
                    $this->filterByRelation($q, $fieldName, $comparator, $value);
                });
            }
        }
    }

    private function filterByField($field, $comparator, $value)
    {
        switch ($comparator) {
            case 'null':
                $this->model = $this->model->whereNull($field);
                break;
            case 'notNull':
                $this->model = $this->model->whereNotNull($field);
                break;
            case 'is':
                $value = $value == 'true' ? true : false;
                $this->model = $this->model->where($field, $value);
                break;
            case 'in':
                $value = explode(',', $value);
                $this->model = $this->model->whereIn($field, $value);
                break;
            case 'notIn':
                $value = explode(',', $value);
                $this->model = $this->model->whereNotIn($field, $value);
                break;
            default:
                $value = ($comparator == 'like' ? '%' . $value . '%' : $value);
                if (!$appendedFields = $this->validateIfFieldIsAppendedField($this->model, $field)) {
                    $this->model = $this->model->where($field, $comparator, $value);
                } else {
                    $this->model = $this->model->where(function ($query) use ($appendedFields, $comparator, $value) {
                        foreach ($appendedFields as $field) {
                            $query->orWhere($field, $comparator, $value);
                        }
                        $query->orWhereRaw('CONCAT_WS(" ",' . implode(',', $appendedFields) . ') ' . $comparator . ' "' . $value . '" ');
                    });
                }
                break;
        }
    }

    private function filterByRelation(&$query, $fieldName, $comparator, $value)
    {
        switch ($comparator) {
            case 'null':
                $query->whereNull($fieldName);
                break;
            case 'notNull':
                $query->whereNotNull($fieldName);
                break;
            case 'is':
                $value = $value == 'true' ? true : false;
                $query->where($fieldName, $value);
                break;
            case 'in':
                $value = explode(',', $value);
                $query->whereIn($fieldName, $value);
                break;
            case 'notIn':
                $value = explode(',', $value);
                $query->whereNotIn($fieldName, $value);
                break;
            default:
                if (!$appendedFields = $this->validateIfFieldIsAppendedField($query, $fieldName)) {
                    $value = ($comparator == 'like' ? '%' . $value . '%' : $value);
                    $query->where($fieldName, $comparator, $value);
                } else {
                    $query->where(function ($q) use ($appendedFields, $comparator, $value) {
                        $value = ($comparator == 'like' ? '%' . $value . '%' : $value);
                        foreach ($appendedFields as $field) {
                            $q->orWhere($field, $comparator, $value);
                        }
                        $q->orWhereRaw('CONCAT_WS(" ",' . implode(',', $appendedFields) . ') ' . $comparator . ' "' . $value . '" ');
                    });
                }
                break;
        }


    }

    private function validateIfFieldIsAppendedField($query, $field)
    {
        $model = $query->getModel();

        if ($model->appendedFields && array_key_exists($field, $model->appendedFields))
            return $model->appendedFields[$field];
        return null;
    }

    private function validateIfCanFilterByField($field)
    {
        return in_array($field, $this->searchableFields);
    }
}
