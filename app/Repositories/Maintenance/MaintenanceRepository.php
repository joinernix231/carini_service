<?php

namespace App\Repositories\Maintenance;

use App\Events\Maintenance\MaintenanceAssigned;
use App\Events\Maintenance\MaintenanceCancelled;
use App\Events\Maintenance\MaintenanceConfirmed;
use App\Events\Maintenance\MaintenanceCreated;
use App\Events\Maintenance\UploadPriceMaintenance;
use App\Events\Maintenance\UploadSupportPaymentMaintenance;
use App\Events\Maintenance\VerifyPaymentMaintenance;
use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\BaseRepository;


class MaintenanceRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'created_at',
        'status',
        'date_maintenance',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Maintenance::class;
    }

    public function create(array $input)
    {
        $input['status'] = 'pending';

        $maintenance = parent::create($input);

        event(new MaintenanceCreated($maintenance));

        return $maintenance;
    }

    public function assignTechnician(Maintenance $maintenance, Technician $technician, string $shift): Maintenance
    {
        $maintenance->technician_id = $technician->id;
        $maintenance->shift = $shift;
        $maintenance->status = 'assigned';
        $maintenance->save();

        event(new MaintenanceAssigned($maintenance));

        return $maintenance;
    }

    public function assignMaintenance(Maintenance $maintenance, array $input): Maintenance
    {
        $maintenance->status = 'assigned';
        $maintenance->technician_id = $input['technician_id'];
        $maintenance->date_maintenance = $input['date_maintenance'];
        $maintenance->value = $input['value'] ?? null;
//        $maintenance->spare_parts = $input['spare_parts'];
        $maintenance->shift = $input['shift'];

        if (!empty($input['value'])) {
            $maintenance->is_paid = false;
        }

        // Activar confirmación requerida
        $maintenance->confirmation_required = true;
        $maintenance->confirmation_deadline = now()->addHour();
        $maintenance->confirmed_at = null;
        $maintenance->coordinator_notified = false;
        $maintenance->coordinator_notified_at = null;

        $maintenance->save();

        event(new MaintenanceAssigned($maintenance));

        return $maintenance;
    }

    public function verifyPayment(Maintenance $maintenance): Maintenance
    {
        $maintenance->is_paid = true;
        $maintenance->save();

        event(new VerifyPaymentMaintenance($maintenance));

        return $maintenance;
    }

    public function uploadPrice($input, Maintenance $maintenance): Maintenance
    {
        $isPaid = $input['is_paid'] ?? null;
        $value = $input['value'] ?? null;
        $priceSupport = $input['price_support'] ?? null;

        // Si no requiere pago
        if (is_null($isPaid)) {
            $maintenance->status = 'quoted';
            $maintenance->save();
            return $maintenance;
        }

        // Si requiere pago
        $maintenance->status = 'quoted';
        $maintenance->is_paid = $isPaid;
        $maintenance->value = $value;
        $maintenance->price_support = $priceSupport;
        $maintenance->save();

        event(new UploadPriceMaintenance($maintenance));

        return $maintenance;
    }

    public function updatePaymentSupport(Maintenance $maintenance, string $paymentSupportFileName): Maintenance
    {
        $maintenance->payment_support = $paymentSupportFileName;
        $maintenance->save();

        event(new UploadSupportPaymentMaintenance($maintenance));

        return $maintenance;
    }

    public function updateQuotation(array $input, Maintenance $maintenance): Maintenance
    {
        $isPaid = $input['is_paid'] ?? null;
        $value = $input['value'] ?? null;
        $priceSupport = $input['price_support'] ?? null;

        // Si no requiere pago
        if (is_null($isPaid)) {
            $maintenance->is_paid = null;
            $maintenance->value = null;
            $maintenance->price_support = null;
            $maintenance->status = 'quoted';
            $maintenance->save();
            return $maintenance;
        }

        // Si requiere pago
        $maintenance->is_paid = $isPaid;
        $maintenance->value = $value;
        $maintenance->price_support = $priceSupport;
        $maintenance->status = 'quoted';
        $maintenance->save();

        event(new UploadPriceMaintenance($maintenance));

        return $maintenance;
    }

    public function AssociateMaintenanceDevice ($clientDevices, $maintenance): void
    {
        $pivotData = [];
        foreach ($clientDevices as $device) {
            $pivotData[$device['id']] = [
                'description' => $device['description'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $maintenance->clientDevices()->attach($pivotData);
    }

    /**
     * Confirmar mantenimiento por parte del cliente
     * Actualiza el mantenimiento y dispara el evento MaintenanceConfirmed
     */
    public function confirm(Maintenance $maintenance): Maintenance
    {
        // Actualizar campos de confirmación
        $maintenance->confirmed_at = now();
        $maintenance->confirmation_required = false;
        $maintenance->save();

        // Disparar evento para que los listeners manejen las notificaciones
        event(new MaintenanceConfirmed($maintenance));

        return $maintenance;
    }

    /**
     * Obtener mantenimientos sin confirmar que requieren notificación al coordinador
     * Busca mantenimientos donde:
     * - status = 'assigned'
     * - confirmation_required = true
     * - confirmed_at = null
     * - confirmation_deadline <= now()
     * - coordinator_notified = false
     */
    public function getUnconfirmedMaintenancesRequiringNotification()
    {
        return $this->model
            ->where('status', 'assigned')
            ->where('confirmation_required', true)
            ->whereNull('confirmed_at')
            ->where('confirmation_deadline', '<=', now())
            ->where('coordinator_notified', false)
            ->with([
                'clientDevices.client',
                'clientDevices.device',
                'technician.user'
            ])
            ->get();
    }

    /**
     * Marcar mantenimiento como notificado al coordinador
     */
    public function markCoordinatorNotified(Maintenance $maintenance): Maintenance
    {
        $maintenance->coordinator_notified = true;
        $maintenance->coordinator_notified_at = now();
        $maintenance->save();

        return $maintenance;
    }

    /**
     * Cancelar mantenimiento y liberar técnico asignado
     * Cambia el status a 'cancelled' y libera el técnico
     *
     * @param Maintenance $maintenance
     * @return Maintenance
     */
    public function cancel(Maintenance $maintenance): Maintenance
    {
        // Cambiar status a cancelled
        $maintenance->status = 'cancelled';

        // Liberar técnico asignado
        $maintenance->technician_id = null;

        // Limpiar campos relacionados con la asignación
        $maintenance->date_maintenance = null;
        $maintenance->shift = null;

        // Limpiar campos de confirmación
        $maintenance->confirmation_required = false;
        $maintenance->confirmed_at = null;
        $maintenance->confirmation_deadline = null;
        $maintenance->coordinator_notified = false;
        $maintenance->coordinator_notified_at = null;

        $maintenance->save();

        // Disparar evento para que los listeners manejen las notificaciones
        event(new MaintenanceCancelled($maintenance));

        return $maintenance;
    }

}
