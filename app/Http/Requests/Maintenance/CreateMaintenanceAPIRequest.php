<?php

namespace App\Http\Requests\Maintenance;

use App\Http\Requests\APIRequest;
use App\Models\Maintenance\Maintenance;
use App\Repositories\Technician\TechnicianRepository;
use App\Rules\Maintenance\UniquePendingMaintenance;
use App\Rules\Technician\ValidateDateOfTechnician;
use Illuminate\Validation\Rule;

class CreateMaintenanceAPIRequest extends APIRequest
{
    public function __construct(private readonly TechnicianRepository $technicianRepository)
    {}

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = Maintenance::$rules;


        $rules['maintenance_type_id'] = ['nullable'];

        $rules['client_device_id'] = ['integer', $this->existsRule('client_device'), new UniquePendingMaintenance()];

        $rules['date_maintenance'] = [
            Rule::requiredIf(fn() => $this->input('type') === 'preventive'),
            'nullable', 'date', new ValidateDateOfTechnician($this->technicianRepository),
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'maintenance_type_id.exists' => 'El tipo de mantenimiento seleccionado no existe.',
            'client_device_id.exists' => 'El equipo seleccionado no existe en la base de datos.',
        ];
    }

}
