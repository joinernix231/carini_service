<?php

namespace App\Repositories\Device;

use App\Models\Client\Client;
use App\Models\Device\Device;
use App\Repositories\BaseRepository;


class DeviceRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Device::class;
    }
}
