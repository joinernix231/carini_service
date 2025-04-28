<?php

namespace App\Rules\Utils;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class ObjectBelongsToModelRule implements ValidationRule
{
    public function __construct(protected Model $model, protected string $relation, protected string $modelName, protected bool $create = false, protected string $field = 'id') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $belongs = $this->objectBelongsToModelRule($value);

        if ($this->create && $belongs) {
            $fail($this->message());
        }

        if (!$belongs) {
            $fail($this->message());
        }
    }

    public function message(): string
    {
        if ($this->create) {
            return 'Este(a) :attribute ya pertenece a este(a) '.$this->modelName;
        }

        return 'Este(a) :attribute no pertenece a este(a) '.$this->modelName;
    }

    private function objectBelongsToModelRule($id): bool
    {
        return $this->model->{$this->relation}()->where($this->field, $id)->exists();
    }
}
