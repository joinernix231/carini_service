<?php

namespace App\Http\Requests\Concerns;

use App\Models\Maintenance\Maintenance;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Reglas compartidas para las fotos que sube el técnico:
 * - el equipo debe pertenecer a ESTE mantenimiento (antes bastaba con que
 *   existiera en client_device, así que se podían colgar fotos en órdenes ajenas);
 * - tope de fotos por petición y por mantenimiento.
 */
trait LimitsMaintenancePhotos
{
    protected function deviceOfThisMaintenanceRule(): Exists
    {
        $maintenance = $this->route('maintenance');

        return Rule::exists('maintenance_client_device', 'client_device_id')
            ->where('maintenance_id', $maintenance->id ?? 0);
    }

    protected function maxPhotosPerRequest(): int
    {
        return (int) config('app.maintenance_max_photos_per_request', 30);
    }

    protected function checkMaintenancePhotoTotal($validator, string $field): void
    {
        $validator->after(function ($validator) use ($field) {
            $maintenance = $this->route('maintenance');
            $incoming = $this->input($field);

            if (!$maintenance instanceof Maintenance || !is_array($incoming) || $incoming === []) {
                return;
            }

            $max = (int) config('app.maintenance_max_photos', 150);
            $current = $maintenance->photos()->count();

            if ($current + count($incoming) > $max) {
                $validator->errors()->add(
                    $field,
                    "El mantenimiento ya tiene {$current} fotos; el máximo es {$max}."
                );
            }
        });
    }
}
