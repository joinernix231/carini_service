<?php

namespace App\Repositories\LinkDevice;

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
        return ClientDevice::class; // TODO Crear Modelo Equipo-Cliente
    }
}
