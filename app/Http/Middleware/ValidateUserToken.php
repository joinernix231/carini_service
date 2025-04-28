<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedRequestException;
use App\Models\Agent\Agent;
use App\Models\Client\Client;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

readonly class ValidateUserToken
{

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $user = Auth::user();


        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Este usuario no tiene permiso para realizar esta acción (1)',
            ], 401);
        }
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Este usuario no tiene permiso para realizar esta acción (2)',
            ], 401);
        }


        /** @var User $user */
        session(['user_id' => $user->id, 'user' => $user]);

        return $next($request);
    }


    protected function throwUnauthorizedException(string $message, Request $request): void
    {
        throw new UnauthorizedRequestException($message, 401, $request);
    }

    private function getUserById(int $id): ?Client
    {
        return User::query()->where('id', $id)->first();
    }
}
