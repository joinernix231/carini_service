<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedRequestException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class ValidateUserToken
{

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Este usuario no tiene permiso para realizar esta acción',
            ], 401);
        }

        return $next($request);
    }


    protected function throwUnauthorizedException(string $message, Request $request): void
    {
        throw new UnauthorizedRequestException($message, 401, $request);
    }
}
