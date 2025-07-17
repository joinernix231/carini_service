<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedRequestException;
use App\Models\Agent\Agent;
use App\Models\Client\Client;
use App\Models\User;
use App\Repositories\Client\ClientRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

readonly class ValidateUserToken
{

    public function __construct(protected readonly ClientRepository $clientRepository)
    {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        /**@var User $user**/
        $user = Auth::user();

        if (!$token) {
            $this->throwUserDoesNotHaveAccessResponse('Este usuario no tiene permiso para realizar esta acción(1)', $request);
        }
        if (!$user) {
            $this->throwUserDoesNotHaveAccessResponse('Este usuario no tiene permiso para realizar esta acción(2)', $request);
        }

        $client = $this->clientRepository->getClientByUserId($user->id);

        session(['user_id' => $user->id, 'user' => $user, 'client' => $client, 'client_id' => $client->id]);

        return $next($request);
    }


    protected function throwUserDoesNotHaveAccessResponse(string $message, Request $request): void
    {
        throw new UnauthorizedRequestException($message, 401, $request);
    }

    private function getUserById(int $id): ?Client
    {
        return User::query()->where('id', $id)->first();
    }
}
