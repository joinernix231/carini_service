<?php

use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Maintenance\MaintenancePDFPreviewController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthAPIController::class, 'login']);

// Rutas para previsualizar y descargar PDFs de mantenimiento (sin middleware)
Route::get('/preview-pdf/maintenance/{id}', [MaintenancePDFPreviewController::class, 'preview'])
    ->name('maintenance.pdf.preview');
Route::get('/download-pdf/maintenance/{id}', [MaintenancePDFPreviewController::class, 'download'])
    ->name('maintenance.pdf.download');

