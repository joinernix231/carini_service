<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use Illuminate\Validation\Rule;

class UpdateQuotationAPIRequest extends APIRequest
{
    public function validationData(): array
    {
        $maintenance = $this->route('maintenance');
        $this->addParametersToRequest(['maintenance_id' => $maintenance->id]);

        return $this->all();
    }

    public function authorize(): bool
    {
        $allowedRoles = ['coordinador', 'administrador'];
        $user = session('user');

        return in_array($user->role, $allowedRoles);
    }

    public function rules(): array
    {
        /**@var Maintenance $maintenance*/
        $maintenance = $this->route('maintenance');

        $rules = [
            'is_paid' => [
                'bail',
                'nullable',
                'boolean',
                function ($attribute, $value, $fail) {
                    // Si is_paid es true, no se puede cambiar (ya está pagado)
                    if ($value === true) {
                        $fail('No se puede cambiar el estado de pago a pagado. El mantenimiento ya está pagado.');
                    }
                },
                function ($attribute, $value, $fail) use ($maintenance) {
                    // Verificar que el mantenimiento esté en estado 'quoted'
                    if ($maintenance->status !== 'quoted') {
                        $fail('Solo se pueden editar cotizaciones de mantenimientos en estado "quoted".');
                    }
                },
                function ($attribute, $value, $fail) use ($maintenance) {
                    // Verificar que no tenga técnico asignado
                    if (in_array($maintenance->status, ['assigned', 'in_progress', 'completed'])) {
                        $fail('No se puede editar la cotización de un mantenimiento que ya tiene técnico asignado.');
                    }
                }
            ],
            'value' => [
                'bail',
                'nullable',
                'numeric',
                'min:0',
            ],
            'price_support' => [
                'bail',
                'nullable',
                'string',
                'required_if:is_paid,false'
            ],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'is_paid.boolean' => 'El campo is_paid debe ser un valor booleano.',
            'value.numeric' => 'El valor debe ser un número.',
            'value.min' => 'El valor debe ser mayor o igual a 0.',
            'price_support.string' => 'El soporte de precio debe ser una cadena de texto.',
            'price_support.required_if' => 'El soporte de precio es requerido cuando el mantenimiento requiere pago.',
        ];
    }
}
