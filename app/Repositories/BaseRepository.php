<?php

namespace App\Repositories;

abstract class BaseRepository extends InfyomBaseRepository
{

    protected bool $skipEvents = false;

    public function skipEvents($status = true)
    {
        $this->skipEvents = $status;
        return $this;
    }

    public function create(array $attributes)
    {
        $model = parent::create($attributes);

        if (!$this->skipEvents)
            $this->fireEvents($model->events, 'created', $model);
        return $model;
    }


    public function update(array $attributes, $id)
    {
        $model = parent::update($attributes, $id);

        if (!$this->skipEvents)
            $this->fireEvents($model->events, 'updated', $model);
        return $model;
    }

    public function delete($id)
    {
        $model = clone $this->find($id);

        $deleted = parent::delete($id);

        if (!$this->skipEvents)
            $this->fireEvents($model->events, 'deleted', $model);

        return $deleted;
    }

    public function fireEvents($events, $method, $model)
    {
        if (is_null($events))
            return true;

        if (!isset($events[$method]))
            return true;

        foreach ($events[$method] as $event)
            event(new $event($model));
    }

    public function updateNotFillableFields($model, array $data)
    {
        foreach ($data as $field => $value) {
            $model->{$field} = $value;
        }
        $model->save();
        return $model;
    }

    /**
     * all() con tope de filas para los listados con ?unpaginated.
     * Antes devolvían la tabla entera (con N+1 en los Resources); el tope se
     * configura con UNPAGINATED_MAX (por defecto 500).
     */
    public function allCapped(?int $max = null)
    {
        $max = $max ?? (int) config('app.unpaginated_max', 500);

        $results = $this->scopeQuery(fn ($query) => $query->limit($max))->all();

        if ($results->count() >= $max) {
            \Illuminate\Support\Facades\Log::warning('Listado unpaginated truncado al tope', [
                'repository' => static::class,
                'max' => $max,
            ]);
        }

        return $results;
    }

    public function getDataByField($field, $value, $withTrashed = false)
    {
        $model = $this->model;
        if ($withTrashed)
            $model->withTrashed();
        return $model->where($field, $value)->first();
    }
}
