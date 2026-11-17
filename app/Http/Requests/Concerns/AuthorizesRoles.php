<?php

namespace App\Http\Requests\Concerns;

use App\Models\ClientDevice\ClientDevice;
use App\Models\Maintenance\Maintenance;
use App\Models\User;

trait AuthorizesRoles
{
    protected function currentUser(): ?User
    {
        return session('user');
    }

    protected function userHasRole(string ...$roles): bool
    {
        $user = $this->currentUser();

        return $user !== null && in_array($user->role, $roles, true);
    }

    /**
     * El técnico de la sesión es el asignado al mantenimiento.
     */
    protected function isAssignedTechnician(?Maintenance $maintenance): bool
    {
        return $maintenance !== null
            && $this->userHasRole('tecnico')
            && session('technician_id') !== null
            && (int) $maintenance->technician_id === (int) session('technician_id');
    }

    /**
     * El cliente de la sesión es dueño de al menos un equipo del mantenimiento.
     */
    protected function isMaintenanceOwner(?Maintenance $maintenance): bool
    {
        return $maintenance !== null
            && $this->userHasRole('cliente')
            && session('client_id') !== null
            && $maintenance->clientDevices()->where('client_device.client_id', session('client_id'))->exists();
    }

    /**
     * Administración/coordinación, el técnico asignado o el cliente dueño.
     */
    protected function canAccessMaintenance(?Maintenance $maintenance): bool
    {
        if ($this->userHasRole('administrador', 'coordinador')) {
            return true;
        }

        return $this->isAssignedTechnician($maintenance) || $this->isMaintenanceOwner($maintenance);
    }

    /**
     * El cliente de la sesión es dueño del equipo vinculado.
     */
    protected function isClientDeviceOwner(?ClientDevice $clientDevice): bool
    {
        return $clientDevice !== null
            && session('client_id') !== null
            && (int) $clientDevice->client_id === (int) session('client_id');
    }
}
