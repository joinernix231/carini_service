<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompanyAPIController extends Controller
{
    public function __construct(private readonly ClientRepository $clientRepository)
    {}
    public function index(ReadClientAPIRequest $request): JsonResponse
    {
        $clients = $request->has('unpaginated') ?
            $this->clientRepository->all() :
            $this->clientRepository->paginate(20);

        return $this->makeResponseResource('Clients retrieved Successfully', ClientResource::collection($clients));

    }

    public function store(CreateClientAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $client = $this->clientRepository->create($input);

        return $this->makeResponseResource('Client created Successfully', new ClientResource($client));
    }

    public function show(Client $client, ShowClientAPIRequest $request): JsonResponse
    {
        return $this->makeResponseResource('Clients retrieved Successfully', new ClientResource($client));
    }


    public function update(Client $client,UpdateClientAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $client = $this->clientRepository->update($input, $client->id);

        return $this->makeResponseResource('Clients updated Successfully', new ClientResource($client));
    }

    public function destroy(DeleteClientAPIRequest $request,int $id)
    {
        //
    }
}
