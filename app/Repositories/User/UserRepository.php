<?php

namespace App\Repositories\User;

use App\Models\Client\Client;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;


class UserRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return User::class;
    }


    public function createClient(array $attributes)
    {
        $userData = Arr::only($attributes, ['email', 'identifier', 'name']);

        $userData['role'] = 'cliente';

        $userData['password'] = bcrypt($userData['identifier']);

        return parent::create($userData);
    }
}
