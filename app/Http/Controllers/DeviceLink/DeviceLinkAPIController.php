<?php

namespace App\Http\Controllers\DeviceLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientDevice\CreateClientDeviceAPIRequest;
use App\Http\Requests\ClientDevice\DeleteClientDeviceAPIRequest;
use App\Http\Requests\ClientDevice\ReadClientDeviceAPIRequest;
use App\Http\Requests\ClientDevice\ShowClientDeviceAPIRequest;
use App\Http\Requests\ClientDevice\UpdateClientDeviceAPIRequest;
use App\Http\Resources\ClientDevice\ClientDeviceResource;
use App\Models\ClientDevice\ClientDevice;
use App\Repositories\Device\DeviceRepository;
use App\Repositories\LinkDevice\LinkDeviceRepository;
use App\Utils\Criterias\BasicCriteria\OrderByCriteria;
use App\Utils\Criterias\BasicCriteria\WhereFieldCriteria;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeviceLinkAPIController extends Controller
{

    public function __construct(private readonly LinkDeviceRepository $linkDeviceRepository, private readonly DeviceRepository $deviceRepository)
    {}

    public function index(ReadClientDeviceAPIRequest $request): JsonResponse
    {

        $this->linkDeviceRepository->pushCriteria(new OrderByCriteria('updated_at', 'desc'));
        $this->linkDeviceRepository->pushCriteria(new WhereFieldCriteria('client_id', session('client_id')));

        $clientDevice = $request->has('unpaginated') ?
            $this->linkDeviceRepository->all() :
            $this->linkDeviceRepository->paginate(20);
        $clientDevice->load(['client','device']);


        return $this->makeResponseResource('ClientsDevices retrieved Successfully', ClientDeviceResource::collection($clientDevice));

    }

    public function store(CreateClientDeviceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();
        $deviceId = $this->deviceRepository->findBySerial($input['serial']);

        $clientDevice = $this->linkDeviceRepository->linkDevice($input, $deviceId);

        return $this->makeResponseResource('ClientDevice created Successfully', new ClientDeviceResource($clientDevice));
    }
    public function show(ClientDevice $linkDevice, ShowClientDeviceAPIRequest $request): JsonResponse
    {

        $linkDevice->load(['client','device']);


        return $this->makeResponseResource('ClientDevice retrieved Successfully', new ClientDeviceResource($linkDevice));
    }

    public function update(ClientDevice $linkDevice, UpdateClientDeviceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $clientDevice = $this->linkDeviceRepository->update($input,$linkDevice->id,);

        return $this->makeResponseResource('ClientDevice Updated Successfully', new ClientDeviceResource($clientDevice));
    }

    public function destroy(ClientDevice $linkDevice, DeleteClientDeviceAPIRequest $request): JsonResponse
    {
        $this->linkDeviceRepository->delete($linkDevice->id);

        return $this->makeResponse('ClientDevice Deleted Successfully',[]);
    }
}
