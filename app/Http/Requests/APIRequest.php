<?php

namespace App\Http\Requests;
/**
 * Created by PhpStorm.
 * User: Juan Paz
 * Date: 18/08/2017
 * Time: 7:11 PM
 */

use App\Utils\Resources\ResponseUtil;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;


class APIRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(ResponseUtil::makeError(message: 'Ha ocurrido un error de validación', code: 400, data: $errors));
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(ResponseUtil::makeError(message: 'Este usuario no tiene permiso para realizar esta acción', code: 403));
    }

    /**
     * Throw an exception when a requested model does not exist.
     *
     * @throws HttpResponseException
     */
    public function failedExists(string $modelName): void
    {
        throw new HttpResponseException(ResponseUtil::makeError(message: "Este $modelName no se ha encontrado.", code: 404));
    }

    public function addParametersToRequest(array $params): void
    {
        $this->merge($params);
    }

    /**
     * Get the validation messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'required' => 'El valor :attribute es requerido',
            'required_if' => 'El valor :attribute es requerido cuando :other es :value',

            'exists' => 'El valor :attribute no existe',
            'unique' => 'El valor :attribute ya existe',
            'in' => 'El valor :attribute debe ser uno de los siguientes valores: :values',

            'integer' => 'El valor :attribute debe ser un número entero',
            'numeric' => 'El valor :attribute debe ser numérico',
            'string' => 'El valor :attribute debe ser un texto',
            'boolean' => 'El valor :attribute debe ser verdadero o falso',
            'date' => 'El valor :attribute debe ser una fecha válida',
            'email' => 'El valor :attribute debe ser un correo electrónico válido',
            'confirmed' => 'El valor :attribute no coincide con la confirmación',

            'min' => [
                'numeric' => 'El valor :attribute debe ser al menos :min.',
                'file' => 'El archivo :attribute debe tener al menos :min kilobytes.',
                'string' => 'El valor :attribute debe tener al menos :min caracteres.',
                'array' => 'El valor :attribute debe tener al menos :min elementos.',
            ],

            'max' => [
                'numeric' => 'El valor :attribute no debe ser mayor que :max.',
                'file' => 'El archivo :attribute no debe pesar más de :max kilobytes.',
                'string' => 'El valor :attribute no debe tener más de :max caracteres.',
                'array' => 'El valor :attribute no debe contener más de :max elementos.',
            ],

            'between' => [
                'numeric' => 'El valor :attribute debe estar entre :min y :max.',
                'file' => 'El archivo :attribute debe tener entre :min y :max kilobytes.',
                'string' => 'El valor :attribute debe tener entre :min y :max caracteres.',
                'array' => 'El valor :attribute debe tener entre :min y :max elementos.',
            ],
        ];
    }

    public function existsRule(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(function ($q) {
            $q->where('deleted_at', null);
        });
    }

    public function uniqueRule(string $table, string $column = 'id', $ignoreId = null, $ignoreColumn = 'id'): Unique
    {
        $rule = Rule::unique($table, $column)
            ->where(fn($q) => $q->whereNull('deleted_at'));

        if ($ignoreId) {
            $rule->ignore($ignoreId, $ignoreColumn);
        }

        return $rule;
    }
}
