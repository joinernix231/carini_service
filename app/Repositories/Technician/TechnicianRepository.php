<?php

namespace App\Repositories\Technician;

use App\Models\Maintenance\MaintenanceType;
use App\Models\Technician\Technician;
use App\Repositories\BaseRepository;
use Illuminate\Support\Carbon;


class TechnicianRepository extends BaseRepository
{
    protected $fieldSearchable = [];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Technician::class;
    }

    public function getAvailableTechnicianForShift(string $date): ?array
    {
        $technicians = $this->model
            ->where('status', 'active')
            ->with(['maintenances' => function ($query) use ($date) {
                $query->whereDate('date_maintenance', $date);
            }])
            ->get();

        foreach ($technicians as $technician) {
            $shifts = $technician->maintenances->pluck('shift')->toArray();

            $hasAM = in_array('AM', $shifts);
            $hasPM = in_array('PM', $shifts);

            if (!$hasAM) {
                return ['technician' => $technician, 'shift' => 'AM'];
            }

            if (!$hasPM) {
                return ['technician' => $technician, 'shift' => 'PM'];
            }
        }

        return null;
    }

    public function getAvailableDates(int $dias = 15): array
    {
        $today = Carbon::today();
        $availableDates = [];

        for ($i = 1; $i < $dias; $i++) {
            $date = $today->copy()->addDays($i);

            if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                continue;
            }

            $available = $this->getAvailableTechnicianForShift($date->format('Y-m-d'));

            if ($available !== null) {
                $availableDates[] = $date->format('Y-m-d');
            }
        }

        return $availableDates;
    }

}
