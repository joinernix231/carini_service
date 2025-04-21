<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CreateClientAPIRequest;
use App\Http\Requests\Client\DeleteClientAPIRequest;
use App\Http\Requests\Client\ReadClientAPIRequest;
use App\Http\Requests\Client\ShowClientAPIRequest;
use App\Http\Requests\Client\UpdateClientAPIRequest;
use App\Http\Resources\Client\ClientResource;
use App\Repositories\Client\ClientRepository;
use Illuminate\Http\JsonResponse;


class ClientAPIController extends Controller
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

        return $this->makeResponseResource('Clients created Successfully', new ClientResource($client));
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowClientAPIRequest $request, int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClientAPIRequest $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteClientAPIRequest $request,int $id)
    {
        //
    }
}
