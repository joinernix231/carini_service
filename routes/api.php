<?php

use App\Http\Controllers\Admin\AdminDeviceLinkAPIController;
use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\ProfileAPIController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Client\ClientAPIController;
use App\Http\Controllers\Client\InactiveClientAPIController;
use App\Http\Controllers\Coordinator\CoordinatorAPIController;
use App\Http\Controllers\Coordinator\InactiveCoordinatorAPIController;
use App\Http\Controllers\Device\DeviceAPIController;
use App\Http\Controllers\DeviceLink\DeviceLinkAPIController;
use App\Http\Controllers\Maintenance\MaintenanceAPIController;
use App\Http\Controllers\Maintenance\MaintenanceAssignedAPIController;
use App\Http\Controllers\Maintenance\MaintenanceCoordinatorAPIController;
use App\Http\Controllers\Maintenance\MaintenanceNotAssignedAPIController;
use App\Http\Controllers\Maintenance\MaintenanceNotPriceAPIController;
use App\Http\Controllers\Maintenance\MaintenancesByPaymentStatusAPIController;
use App\Http\Controllers\Maintenance\UpdatePaymentSupportAPIController;
use App\Http\Controllers\Other\ResourceAPIController;
use App\Http\Controllers\Technician\AvailableTechnicianAPIController;
use App\Http\Controllers\Technician\AvailableTechnicianController;
use App\Http\Controllers\Technician\InactiveTechnicianAPIController;
use App\Http\Controllers\Technician\TechnicianAPIController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
// Auth
Route::post('/acceptPolicy', AuthAPIController::class . '@acceptPolicy');
Route::get('/me', MeController::class . '@me');
Route::put('/profile', ProfileAPIController::class . '@update');
// Clients
Route::apiResource('/clients', ClientAPIController::class);
    Route::put('/clients/{client}/status', InactiveClientAPIController::class);
// LinkDevices
Route::apiResource('/linkDevices', DeviceLinkAPIController::class);
// Admin LinkDevices
Route::apiResource('/admin/clients/{client}/devices', AdminDeviceLinkAPIController::class)
    ->only(['store', 'destroy'])
    ->names([
        'store' => 'admin.clients.devices.store',
        'destroy' => 'admin.clients.devices.destroy'
    ]);
// Devices
Route::apiResource('/devices', DeviceAPIController::class);
// Maintenance
Route::apiResource('/maintenances', MaintenanceAPIController::class);
    Route::apiResource('/maintenancesCoordinator', MaintenanceCoordinatorAPIController::class);
Route::get('/maintenancesNotAssigned', MaintenanceNotAssignedAPIController::class);
Route::get('/maintenancesAssigned', MaintenanceAssignedAPIController::class);
Route::get('/maintenancesNotPrice', MaintenanceNotPriceAPIController::class);
Route::get('/maintenancesByPaymentStatus', MaintenancesByPaymentStatusAPIController::class);
Route::put('/verifyMaintenance/{maintenance}', MaintenanceAssignedAPIController::class . '@verifyPaymentOfMaintenance');
Route::put('/assignMaintenance/{maintenance}', MaintenanceAssignedAPIController::class . '@assignMaintenance');
Route::put('/maintenances/{maintenance}/payment-support', UpdatePaymentSupportAPIController::class);
Route::put('/uploadPrice/{maintenance}', MaintenanceAssignedAPIController::class . '@uploadPrice');
Route::put('/maintenance/{maintenance}/update-quotation', MaintenanceAPIController::class . '@updateQuotation');

// Other
Route::post('loadImage', ResourceAPIController::class . '@loadImage');
Route::post('loadDoc', ResourceAPIController::class . '@loadDoc');
// Technician
Route::get('/availableDates', AvailableTechnicianController::class);
Route::get('/availableTechnicians', AvailableTechnicianAPIController::class);
Route::apiResource('/technical', TechnicianAPIController::class);
Route::put('/technical/{technical}/status', InactiveTechnicianAPIController::class);
//Coordinators
Route::post('/coordinators', CoordinatorAPIController::class . '@store');
Route::get('/coordinators', CoordinatorAPIController::class . '@index');
Route::put('/coordinators/{coordinator}', CoordinatorAPIController::class . '@update');
Route::get('/coordinators/{coordinator}', CoordinatorAPIController::class . '@show');
Route::delete('/coordinators/{coordinator}', CoordinatorAPIController::class . '@destroy');
Route::put('/coordinator/{coordinator}/status', InactiveCoordinatorAPIController::class);

// Notificaciones Push
Route::prefix('notifications')->group(function () {
    Route::post('/register-token', [NotificationController::class, 'registerToken']);
    Route::delete('/unregister-token', [NotificationController::class, 'unregisterToken']);
    Route::post('/send', [NotificationController::class, 'sendNotification']);
    Route::get('/user-notifications', [NotificationController::class, 'getUserNotifications']);
    Route::put('/mark-as-read/{id}', [NotificationController::class, 'markAsRead']);
    Route::put('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::get('/user-tokens', [NotificationController::class, 'getUserTokens']);
    Route::post('/send-to-role', [NotificationController::class, 'sendToRole']);
});
});


