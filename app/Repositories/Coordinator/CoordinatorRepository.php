<?php

namespace App\Repositories\Coordinator;


use App\Models\Coordinator\Coordinator;
use App\Repositories\BaseRepository;


class CoordinatorRepository extends BaseRepository
{
    protected $fieldSearchable = [

    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Coordinator::class;
    }

}
