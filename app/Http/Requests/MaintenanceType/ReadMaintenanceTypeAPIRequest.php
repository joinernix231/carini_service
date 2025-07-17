<?php

namespace App\Http\Requests\MaintenanceType;



use App\Http\Requests\APIRequest;

class ReadMaintenanceTypeAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
       return true;
    }

}
