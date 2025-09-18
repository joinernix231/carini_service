<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\InactiveClient;
use App\Http\Resources\Client\ClientResource;
use App\Models\Client\Client;
use App\Repositories\Client\ClientRepository;
use Illuminate\Http\JsonResponse;

class InactiveClientAPIController extends Controller
{
    public function __construct(private readonly ClientRepository $ClientRepository)
    {}


    public function __invoke(Client $Client, InactiveClient $request): JsonResponse
    {
        $input = $request->validated();

        $Client = $this->ClientRepository->update($input, $Client->id);

        return $this->makeResponseResource('Client update status successfully', new ClientResource($Client));
    }
}
