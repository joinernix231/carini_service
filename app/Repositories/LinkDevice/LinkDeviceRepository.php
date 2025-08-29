<?php

namespace App\Repositories\LinkDevice;


use App\Http\Requests\ClientDevice\CreateClientDeviceAPIRequest;
use App\Models\ClientDevice\ClientDevice;
use App\Models\Device\Device;
use App\Repositories\BaseRepository;


class LinkDeviceRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ClientDevice::class;
    }

    public function linkDevice(array $input)
    {
        $input['linked_by'] = session('user_id');
        $input['client_id'] = session('client_id');
        $input['status'] = true;


        return parent::create($input);
    }
}
