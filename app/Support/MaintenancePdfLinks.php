<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Enlaces firmados y con vencimiento para las rutas públicas del PDF de la
 * orden de servicio (routes/web.php). Sin firma válida esas rutas responden 403,
 * así que solo deben generarse después de comprobar que el usuario puede ver el
 * mantenimiento.
 */
class MaintenancePdfLinks
{
    /**
     * @return array{preview_url: string, download_url: string, expires_at: string}
     */
    public static function make(int $maintenanceId, ?int $clientDeviceId = null): array
    {
        $expiresAt = now()->addMinutes((int) config('maintenance_pdf.signed_url_ttl', 30));

        $params = ['id' => $maintenanceId];
        if ($clientDeviceId !== null) {
            $params['deviceId'] = $clientDeviceId;
        }

        return [
            'preview_url' => URL::temporarySignedRoute('maintenance.pdf.preview', $expiresAt, $params),
            'download_url' => URL::temporarySignedRoute('maintenance.pdf.download', $expiresAt, $params),
            'expires_at' => $expiresAt->toISOString(),
        ];
    }
}
