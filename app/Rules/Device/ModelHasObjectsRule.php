<?php

namespace App\Rules\Device;

use Illuminate\Contracts\Validation\Rule;

class ModelHasObjectsRule implements Rule
{
    protected $model, $relation, $relationName;

    public function __construct($model, $relation, $relationName)
    {
        $this->model = $model;
        $this->relationName = $relationName;
        $this->relation = $relation;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$this->model)
            return false;

        return !$this->modelHasObjects();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "No se puede eliminar ya que hay $this->relationName asociadas(os) a este(a) :attribute.";
    }

    private function modelHasObjects()
    {
        return $this->model->{$this->relation}()->exists();
    }
}
