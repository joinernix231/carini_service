<?php

namespace App\Repositories\Maintenance;

use App\Events\Maintenance\MaintenanceCreated;
use App\Models\Maintenance\Maintenance;
use App\Models\Technician\Technician;
use App\Repositories\BaseRepository;


class MaintenanceRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'created_at',
        'status'
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

        $maintenance->save();

        return $maintenance;
    }

    public function verifyPayment(Maintenance $maintenance): Maintenance
    {
        $maintenance->is_paid = true;
        $maintenance->save();

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

        return $maintenance;
    }

    public function updatePaymentSupport(Maintenance $maintenance, string $paymentSupportFileName): Maintenance
    {
        $maintenance->payment_support = $paymentSupportFileName;
        $maintenance->save();

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

}
