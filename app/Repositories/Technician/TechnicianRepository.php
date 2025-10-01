<?php

namespace App\Repositories\Technician;

use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\BaseRepository;
use Illuminate\Support\Carbon;


class TechnicianRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name'
    ];

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

    /**
     * Obtener técnicos disponibles para una fecha y turno específicos
     *
     * @param string|null $date Fecha en formato Y-m-d
     * @param string|null $shift Turno (AM o PM)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableTechnicians(?string $date = null, ?string $shift = null)
    {
        // Obtener todos los técnicos activos con rol 'tecnico'
        $query = $this->model
            ->where('status', 'active')
            ->whereHas('user', function ($q) {
                $q->where('role', 'tecnico');
            })
            ->with('user');

        // Si no se proporcionan fecha y turno, retornar todos los técnicos
        if (!$date || !$shift) {
            return $query->get();
        }

        // Obtener IDs de técnicos que tienen mantenimientos asignados para la fecha y turno específicos
        $busyTechnicianIds = Maintenance::where('date_maintenance', $date)
            ->where('shift', $shift)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->pluck('technician_id')
            ->filter()
            ->toArray();

        // Excluir técnicos ocupados
        if (!empty($busyTechnicianIds)) {
            $query->whereNotIn('id', $busyTechnicianIds);
        }

        return $query->get();
    }

    public function getTechnicianByUserId(int $userId): ?Technician
    {
        return $this->model
            ->where('user_id', $userId)
            ->first();
    }



}
