<?php

namespace App\Http\Middleware;

use App\Utils\Resources\ResponseUtil;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if ($user && $user->must_change_password) {
            // Rutas permitidas incluso si debe cambiar contraseña
            $allowedRoutes = [
                'api/me',
                'api/change-password',
            ];
            
            $currentPath = $request->path();
            
            // Verificar si la ruta actual está permitida
            $isAllowed = false;
            foreach ($allowedRoutes as $allowedRoute) {
                if (str_starts_with($currentPath, $allowedRoute)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            // Si no está permitida, bloquear el acceso
            if (!$isAllowed) {
                return ResponseUtil::makeError(
                    'Debes cambiar tu contraseña antes de continuar',
                    403,
                    ['must_change_password' => true]
                );
            }
        }
        
        return $next($request);
    }
}
