<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAdminClientDeviceAPIRequest;
use App\Http\Requests\Admin\DeleteAdminClientDeviceAPIRequest;
use App\Http\Resources\ClientDevice\ClientDeviceResource;
use App\Models\Client\Client;
use App\Models\ClientDevice\ClientDevice;
use App\Repositories\LinkDevice\LinkDeviceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdminDeviceLinkAPIController extends Controller
{

    public function __construct(private readonly LinkDeviceRepository $linkDeviceRepository)
    {}

    public function store(Client $client, CreateAdminClientDeviceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();
        $input['client_id'] = $client->id;

        $clientDevice = $this->linkDeviceRepository->linkDeviceByAdmin($input);

        return $this->makeResponseResource('ClientDevice created Successfully', new ClientDeviceResource($clientDevice));
    }

    public function destroy(Client $client, ClientDevice $clientDevice, DeleteAdminClientDeviceAPIRequest $request): JsonResponse
    {
        $this->linkDeviceRepository->delete($clientDevice->id);

        return $this->makeResponse('ClientDevice Deleted Successfully', []);
    }
}
