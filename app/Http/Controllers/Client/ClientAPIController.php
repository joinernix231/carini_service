<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\CreateClientAPIRequest;
use App\Http\Requests\Client\DeleteClientAPIRequest;
use App\Http\Requests\Client\ReadClientAPIRequest;
use App\Http\Requests\Client\ShowClientAPIRequest;
use App\Http\Requests\Client\UpdateClientAPIRequest;
use App\Http\Resources\Client\ClientResource;
use App\Models\Client\Client;
use App\Repositories\Client\ClientRepository;
use App\Repositories\User\UserRepository;
use App\Utils\Criterias\BasicCriteria\FiltersCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;


class ClientAPIController extends Controller
{
    public function __construct(private readonly ClientRepository $clientRepository, private readonly UserRepository $userRepository)
    {}
    public function index(ReadClientAPIRequest $request): JsonResponse
    {
        if ($request->has('filters'))
            $this->clientRepository->pushCriteria(new FiltersCriteria($request->get('filters')));

        $clients = $request->has('unpaginated') ?
            $this->clientRepository->all() :
            $this->clientRepository->paginate(20);

        $clients->load('user');

        return $this->makeResponseResource('Clients retrieved Successfully', ClientResource::collection($clients));

    }

    public function store(CreateClientAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $user = $this->userRepository->createClient($input);

        $clientData = Arr::only($input, ['identifier', 'name', 'address', 'city', 'phone']);
        $clientData['user_id'] = $user->id;

        $client = $this->clientRepository->create($clientData);

        return $this->makeResponseResource('Client created Successfully', new ClientResource($client));
    }


    public function show(Client $client, ShowClientAPIRequest $request): JsonResponse
    {
        $client->load('user');
        return $this->makeResponseResource('Clients retrieved Successfully', new ClientResource($client));
    }


    public function update(Client $client, UpdateClientAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $userData = Arr::only($input, ['email', 'name']);
        if (!empty($userData)) {
            $this->userRepository->update($userData, $client->user_id);
        }

        $clientData = Arr::only($input, ['identifier', 'name', 'address', 'city', 'phone']);

        $client = $this->clientRepository->update($clientData, $client->id);

        return $this->makeResponseResource('Client updated Successfully', new ClientResource($client));
    }

    public function destroy(DeleteClientAPIRequest $request,int $id)
    {
        $this->clientRepository->delete($id);

        return $this->makeResponse('Client deleted Successfully',[$id]);
    }
}
