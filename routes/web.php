<?php

use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Maintenance\MaintenancePDFPreviewController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthAPIController::class, 'login'])->middleware('throttle:10,1');

// PDFs de la orden de servicio. No pasan por ValidateUserToken (se abren en el
// navegador sin el token JWT), así que exigen una URL firmada y con vencimiento:
// sin firma cualquiera podía descargar la orden de cualquier cliente cambiando
// el id. Los enlaces se obtienen con GET /api/maintenances/{maintenance}/pdf-links
// (o en /api/maintenances/{maintenance}/information) y se generan con
// App\Support\MaintenancePdfLinks.
Route::middleware(['signed', 'throttle:30,1'])->group(function () {
    Route::get('/preview-pdf/maintenance/{id}/{deviceId?}', [MaintenancePDFPreviewController::class, 'preview'])
        ->whereNumber(['id', 'deviceId'])
        ->name('maintenance.pdf.preview');
    Route::get('/download-pdf/maintenance/{id}/{deviceId?}', [MaintenancePDFPreviewController::class, 'download'])
        ->whereNumber(['id', 'deviceId'])
        ->name('maintenance.pdf.download');
});
