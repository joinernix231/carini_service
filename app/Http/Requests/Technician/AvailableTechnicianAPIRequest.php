<?php

namespace App\Http\Requests\Technician;

use App\Http\Requests\APIRequest;

class AvailableTechnicianAPIRequest extends APIRequest
{

    public function authorize(): bool
    {
        return true;
    }


}
