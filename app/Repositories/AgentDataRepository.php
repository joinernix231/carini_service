<?php

namespace App\Repositories;

use App\Models\Agent\AgentData;
use GoIdea\BaseService\Repositories\BaseRepository;
use Illuminate\Support\Arr;

/**
 * Class LinkDeviceRepository
 * @package App\Repositories\Agent
 * @version March 19, 2020, 5:52 pm -05
 */
class AgentDataRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'value',
        'agent_field_id',
        'agent_operation_id'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return AgentData::class;
    }

    public function createMany($attributes)
    {
        $attributes = Arr::except($attributes, ['operation_id']);
        $data = Arr::pull($attributes, 'data');
        foreach ($data as $datum) {
            $datum = array_merge($attributes, $datum);
            if (Arr::has($datum, 'id')) {
                $this->update($datum, $datum['id']);
            } else {
                $this->create($datum);
            }
        }
    }

}
