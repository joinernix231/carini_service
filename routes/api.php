<?php

use App\Http\Controllers\Admin\AdminDeviceLinkAPIController;
use App\Http\Controllers\Admin\AdminMaintenanceAPIController;
use App\Http\Controllers\Admin\AdminPasswordResetController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Auth\AuthAPIController;
use App\Http\Controllers\Auth\MeController;
use App\Http\Controllers\Auth\ProfileAPIController;
use App\Http\Controllers\Client\ClientAPIController;
use App\Http\Controllers\Client\InactiveClientAPIController;
use App\Http\Controllers\Coordinator\CoordinatorAPIController;
use App\Http\Controllers\Coordinator\InactiveCoordinatorAPIController;
use App\Http\Controllers\Device\DeviceAPIController;
use App\Http\Controllers\DeviceLink\DeviceLinkAPIController;
use App\Http\Controllers\Maintenance\Client\MaintenanceConfirmationAPIController;
use App\Http\Controllers\Maintenance\Client\MaintenanceInformationAPIController;
use App\Http\Controllers\Maintenance\Coordinator\MaintenanceAssignmentAPIController;
use App\Http\Controllers\Maintenance\Coordinator\MaintenanceCoordinatorActionsAPIController;
use App\Http\Controllers\Maintenance\Coordinator\MaintenanceCoordinatorAPIController;
use App\Http\Controllers\Maintenance\MaintenanceAPIController;
use App\Http\Controllers\Maintenance\Payment\MaintenancePaymentSupportAPIController;
use App\Http\Controllers\Maintenance\Queries\MaintenanceAssignedListAPIController;
use App\Http\Controllers\Maintenance\Queries\MaintenanceByPaymentStatusListAPIController;
use App\Http\Controllers\Maintenance\Queries\MaintenanceNotAssignedListAPIController;
use App\Http\Controllers\Maintenance\Queries\MaintenanceNotPriceListAPIController;
use App\Http\Controllers\Maintenance\Technician\TechnicianMaintenanceActionsAPIController;
use App\Http\Controllers\Maintenance\Technician\TechnicianMaintenancePhotosAPIController;
use App\Http\Controllers\Maintenance\Technician\TechnicianMaintenanceProgressAPIController;
use App\Http\Controllers\Maintenance\Technician\TechnicianMaintenancesAPIController;
use App\Http\Controllers\Maintenance\Technician\TechnicianMaintenanceSparePartsAPIController;
use App\Http\Controllers\Other\ResourceAPIController;
use App\Http\Controllers\Technician\AvailableTechnicianAPIController;
use App\Http\Controllers\Technician\AvailableTechnicianController;
use App\Http\Controllers\Technician\InactiveTechnicianAPIController;
use App\Http\Controllers\Technician\TechnicianAPIController;
use App\Http\Controllers\Technician\TechnicianMaintenancesDetailAPIController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
// Auth
Route::post('/acceptPolicy', AuthAPIController::class . '@acceptPolicy');
Route::post('/change-password', AuthAPIController::class . '@changePassword');
Route::get('/me', MeController::class . '@me');
Route::put('/profile', ProfileAPIController::class . '@update');
// Clients
Route::apiResource('/clients', ClientAPIController::class);
    Route::put('/clients/{client}/status', InactiveClientAPIController::class);
// LinkDevices
Route::apiResource('/linkDevices', DeviceLinkAPIController::class);
Route::get('/linkDevices/{linkDevice}/maintenances', [MaintenanceAPIController::class, 'getLinkDeviceMaintenances'])->name('linkDevices.maintenances');
// Admin LinkDevices
Route::post('/admin/clients/{client}/devices', [AdminDeviceLinkAPIController::class, 'store'])->name('admin.clients.devices.store');
Route::delete('/admin/clients/devices/{clientDevice}', [AdminDeviceLinkAPIController::class, 'destroy'])->name('admin.clients.devices.destroy');
// Admin Maintenances
Route::apiResource('/admin/maintenances', AdminMaintenanceAPIController::class);
Route::get('/admin/clients/devices/{clientDevice}/maintenances', [AdminMaintenanceAPIController::class, 'getClientDeviceMaintenances'])->name('admin.clients.devices.maintenances');
Route::get('/admin/maintenances/{maintenance}/photos', [AdminMaintenanceAPIController::class, 'getMaintenancePhotos'])->name('admin.maintenances.photos');
// Admin Password Reset
Route::put('/admin/clients/{client}/reset-password', [AdminPasswordResetController::class, 'resetClientPassword'])->name('admin.clients.reset-password');
Route::put('/admin/coordinators/{coordinator}/reset-password', [AdminPasswordResetController::class, 'resetCoordinatorPassword'])->name('admin.coordinators.reset-password');
Route::put('/admin/technicians/{technician}/reset-password', [AdminPasswordResetController::class, 'resetTechnicianPassword'])->name('admin.technicians.reset-password');
// Devices
Route::apiResource('/devices', DeviceAPIController::class);
// Maintenance
Route::apiResource('/maintenances', MaintenanceAPIController::class);
    Route::apiResource('/maintenancesCoordinator', MaintenanceCoordinatorAPIController::class);
// Maintenance - Queries (Listados)
Route::get('/maintenancesNotAssigned', MaintenanceNotAssignedListAPIController::class);
Route::get('/maintenancesAssigned', MaintenanceAssignedListAPIController::class);
Route::get('/maintenancesNotPrice', MaintenanceNotPriceListAPIController::class);
Route::get('/maintenancesByPaymentStatus', MaintenanceByPaymentStatusListAPIController::class);
// Maintenance - Coordinator actions (Asignación)
Route::put('/assignMaintenance/{maintenance}', MaintenanceAssignmentAPIController::class . '@assignMaintenance');
Route::put('/verifyMaintenance/{maintenance}', MaintenanceAssignmentAPIController::class . '@verifyPaymentOfMaintenance');
Route::put('/uploadPrice/{maintenance}', MaintenanceAssignmentAPIController::class . '@uploadPrice');
Route::put('/cancelMaintenance/{maintenance}', MaintenanceAssignmentAPIController::class . '@cancelMaintenance');
// Maintenance - Payment actions
Route::put('/maintenances/{maintenance}/payment-support', MaintenancePaymentSupportAPIController::class);
Route::put('/maintenance/{maintenance}/update-quotation', MaintenanceAPIController::class . '@updateQuotation');
// Maintenance - Client actions
Route::get('/maintenances/{maintenance}/information', MaintenanceInformationAPIController::class . '@maintenanceInformation');
Route::post('/maintenances/{maintenance}/confirm', MaintenanceConfirmationAPIController::class . '@confirm');
// Maintenance - Coordinator actions
Route::put('/maintenances/{maintenance}/mark-as-called', MaintenanceCoordinatorActionsAPIController::class . '@markAsCalled');
Route::get('/coordinator/maintenances/unconfirmed', MaintenanceCoordinatorAPIController::class . '@unconfirmed');

// Other
Route::post('loadImage', ResourceAPIController::class . '@loadImage');
Route::post('loadDoc', ResourceAPIController::class . '@loadDoc');
// Technician
Route::get('/availableDates', AvailableTechnicianController::class);
Route::get('/availableTechnicians', AvailableTechnicianAPIController::class);
Route::apiResource('/technical', TechnicianAPIController::class);
Route::put('/technical/{technical}/status', InactiveTechnicianAPIController::class);
// Technician Maintenances - Listado y detalle
Route::get('/technicianMaintenances', TechnicianMaintenancesAPIController::class . '@index');
Route::get('/technicianMaintenances/{maintenance}', TechnicianMaintenancesAPIController::class . '@show');
// Technician Maintenances Detail (for supervisors/coordinators)
Route::get('/maintenancesTechnicians/{technician}', TechnicianMaintenancesDetailAPIController::class . '@index');
Route::get('/maintenancesTechnicians/{technician}/maintenance/{maintenance}', TechnicianMaintenancesDetailAPIController::class . '@show');
// Technician Maintenances - Fotos
Route::post('/technicianMaintenances/{maintenance}/photos', TechnicianMaintenancePhotosAPIController::class . '@uploadPhoto');
// Technician Maintenances - Acciones
Route::post('/technicianMaintenances/{maintenance}/onTheWay', TechnicianMaintenanceActionsAPIController::class . '@onTheWay');
Route::post('/technicianMaintenances/{maintenance}/start', TechnicianMaintenanceActionsAPIController::class . '@startMaintenance');
Route::post('/technicianMaintenances/{maintenance}/pause', TechnicianMaintenanceActionsAPIController::class . '@pauseMaintenance');
Route::post('/technicianMaintenances/{maintenance}/resume', TechnicianMaintenanceActionsAPIController::class . '@resumeMaintenance');
Route::post('/technicianMaintenances/{maintenance}/complete', TechnicianMaintenanceActionsAPIController::class . '@complete');
Route::get('/technician/activeMaintenance', TechnicianMaintenanceActionsAPIController::class . '@activeMaintenance');
// Technician Maintenances - Progreso
Route::post('/technicianMaintenances/{maintenance}/progress', TechnicianMaintenanceProgressAPIController::class . '@saveProgress');
Route::get('/technicianMaintenances/{maintenance}/progress', TechnicianMaintenanceProgressAPIController::class . '@getProgress');
// Technician Maintenances - Repuestos
Route::post('/technicianMaintenances/{maintenance}/suggest-spare-part', TechnicianMaintenanceSparePartsAPIController::class . '@suggestSparePart');
// Technician Maintenances - Reporte de Servicio
Route::post('/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report', \App\Http\Controllers\Maintenance\Technician\TechnicianServiceReportAPIController::class . '@store');
Route::get('/technicianMaintenances/{maintenance}/devices/{clientDevice}/service-report', \App\Http\Controllers\Maintenance\Technician\TechnicianServiceReportAPIController::class . '@show');
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


