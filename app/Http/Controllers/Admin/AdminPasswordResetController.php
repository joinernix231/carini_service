<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Models\Client\Client;
use App\Models\Coordinator\Coordinator;
use App\Models\Technician\Technician;
use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AdminPasswordResetController extends Controller
{
    /**
     * Restaurar la contraseña de un cliente usando su ID
     * La contraseña se restablece a su identifier y se obliga a cambiarla
     *
     * @param Client $client
     * @param AdminResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetClientPassword(Client $client, AdminResetPasswordRequest $request): JsonResponse
    {
        $user = $client->user;

        if (!$user) {
            return ResponseUtil::makeError('El cliente no tiene un usuario asociado', 404);
        }

        // Verificar que el usuario no sea un administrador (por seguridad)
        if ($user->role === 'administrador') {
            return ResponseUtil::makeError('No se puede restaurar la contraseña de un administrador desde este endpoint', 403);
        }

        // Restablecer la contraseña a la identificación y obligar a cambiarla
        $user->update([
            'password' => Hash::make($client->identifier),
            'must_change_password' => true
        ]);

        $message = "Contraseña restaurada exitosamente para el cliente {$user->name}. La contraseña se ha establecido a su identificación y el usuario deberá cambiarla en el próximo inicio de sesión.";

        return ResponseUtil::makeResponse($message, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'identifier' => $client->identifier,
            'must_change_password' => true
        ]);
    }

    /**
     * Restaurar la contraseña de un coordinador usando su ID
     * La contraseña se restablece a su identification y se obliga a cambiarla
     *
     * @param Coordinator $coordinator
     * @param AdminResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetCoordinatorPassword(Coordinator $coordinator, AdminResetPasswordRequest $request): JsonResponse
    {
        $user = $coordinator->user;

        if (!$user) {
            return ResponseUtil::makeError('El coordinador no tiene un usuario asociado', 404);
        }

        // Verificar que el usuario no sea un administrador (por seguridad)
        if ($user->role === 'administrador') {
            return ResponseUtil::makeError('No se puede restaurar la contraseña de un administrador desde este endpoint', 403);
        }

        // Restablecer la contraseña a la identificación y obligar a cambiarla
        $user->update([
            'password' => Hash::make($coordinator->identification),
            'must_change_password' => true
        ]);

        $message = "Contraseña restaurada exitosamente para el coordinador {$user->name}. La contraseña se ha establecido a su identificación y el usuario deberá cambiarla en el próximo inicio de sesión.";

        return ResponseUtil::makeResponse($message, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'identification' => $coordinator->identification,
            'must_change_password' => true
        ]);
    }

    /**
     * Restaurar la contraseña de un técnico usando su ID
     * La contraseña se restablece a su document y se obliga a cambiarla
     *
     * @param Technician $technician
     * @param AdminResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetTechnicianPassword(Technician $technician, AdminResetPasswordRequest $request): JsonResponse
    {
        $user = $technician->user;

        if (!$user) {
            return ResponseUtil::makeError('El técnico no tiene un usuario asociado', 404);
        }

        // Verificar que el usuario no sea un administrador (por seguridad)
        if ($user->role === 'administrador') {
            return ResponseUtil::makeError('No se puede restaurar la contraseña de un administrador desde este endpoint', 403);
        }

        // Restablecer la contraseña a la identificación y obligar a cambiarla
        $user->update([
            'password' => Hash::make($technician->document),
            'must_change_password' => true
        ]);

        $message = "Contraseña restaurada exitosamente para el técnico {$user->name}. La contraseña se ha establecido a su identificación y el usuario deberá cambiarla en el próximo inicio de sesión.";

        return ResponseUtil::makeResponse($message, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'document' => $technician->document,
            'must_change_password' => true
        ]);
    }
}
