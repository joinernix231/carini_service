<?php

namespace App\Http\Requests;
/**
 * Created by PhpStorm.
 * User: Juan Paz
 * Date: 18/08/2017
 * Time: 7:11 PM
 */

use App\Utils\ResponseUtil;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;


class APIRequest extends FormRequest
{

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        throw new HttpResponseException(
            response()->json(
                ResponseUtil::makeError("Ha ocurrido un error de validación", 400, $errors),
                400
            )
        );

    }


    protected function failedAuthorization()
    {
        throw new HttpResponseException(Response::json(ResponseUtil::makeError("Este usuario no tiene permiso para realizar esta acción"), 403));
    }

    public function failedExists($modelName)
    {
        throw new HttpResponseException(Response::json(ResponseUtil::makeError("Este " . $modelName . " no se ha encontrado."), 404));
    }

    public function addParametersToRequest(array $params): void
    {
        $parameters = $this->all();

        foreach ($params as $key => $param) {
            $parameters[$key] = $param;
        }
        $this->replace($parameters);
    }

    public function messages(): array
    {
        $messages = [
            'email' => 'El email no es válido',
            'exists' => 'Este(a) :attribute no existe',
            'in' => 'El campo :attribute debe ser un valor entre :values',
            'integer' => 'El campo :attribute debe ser numérico',
            'numeric' => 'El campo :attribute debe ser numérico',
            'boolean' => 'El campo :attribute debe ser booleano',
            'regex' => 'El valor de :attribute no esta en el formato correcto.',
            'required' => 'El campo :attribute es requerido(a)',
            'string' => 'El campo :attribute debe ser texto',
            'unique' => 'Este(a) :attribute ya ha sido tomado(a)'
        ];
        return $messages;
    }

    public function getData($repository, $id = '')
    {
        $id = !$id ? $this->route('id') : $id;
        return $repository->findWithoutFail($id);
    }

    public function existsRule($table, $column = 'id', $deleted_at = true): \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists($table, $column)->where(function ($q) use ($deleted_at) {
            if ($deleted_at)
                $q->where('deleted_at', null);
        });
    }

    public function uniqueRule($table, $column = 'id', $deleted_at = true): \Illuminate\Validation\Rules\Unique
    {
        return Rule::unique($table, $column)->where(function ($q) use ($deleted_at) {
            if ($deleted_at)
                $q->where('deleted_at', null);
        });
    }

    public function formatDate($dateField): ?string
    {
        $date = null;
        if ($this->has($dateField)) {
            $date = new Carbon($this->get($dateField));
            $date = $date->toDateString();
        }
        return $date;
    }
}
