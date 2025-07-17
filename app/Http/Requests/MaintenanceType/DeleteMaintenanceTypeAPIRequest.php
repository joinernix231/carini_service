<?php

namespace App\Http\Requests\MaintenanceType;

use App\Http\Requests\APIRequest;
use App\Models\Client\Client;
use App\Models\Maintenance\Maintenance;
use Illuminate\Foundation\Http\FormRequest;

class DeleteMaintenanceTypeAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {


        return true;
    }

}
