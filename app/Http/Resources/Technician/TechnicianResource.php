<?php

namespace App\Http\Resources\Technician;

use App\Http\Resources\User\UserResource;
use App\Http\Resources\Maintenance\MaintenanceResource;
use Illuminate\Http\Request;
use App\Http\Resources\BaseJsonResource;

class TechnicianResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'maintenances' => MaintenanceResource::collection($this->whenLoaded('maintenances')),
            'document' => $this->resource->document,
            'phone' => $this->resource->phone,
            'status' => $this->resource->status,
            'address' => $this->resource->address,

            'blood_type' => $this->resource->blood_type,
            'photo' => $this->resource->photo,

            'specialty' => $this->resource->specialty,
            'hire_date' => $this->resource->hire_date,
            'contract_type' => $this->resource->contract_type,

            'eps_pdf' => $this->resource->eps_pdf_url,
            'arl_pdf' => $this->resource->arl_pdf_url,
            'pension_pdf' => $this->resource->pension_pdf_url,

            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
