<?php

namespace App\Repositories\Client;

use App\Models\Client\Client;
use App\Repositories\BaseRepository;


class ClientRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Client::class;
    }
}
