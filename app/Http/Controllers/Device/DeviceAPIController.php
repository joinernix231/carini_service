<?php

namespace App\Http\Controllers\Device;

use App\Http\Controllers\Controller;
use App\Http\Requests\Device\CreateDeviceAPIRequest;
use App\Http\Requests\Device\ReadDeviceAPIRequest;
use App\Http\Requests\Device\ShowDeviceAPIRequest;
use App\Http\Requests\Device\UpdateDeviceAPIRequest;
use App\Http\Resources\Device\DeviceResource;
use App\Models\Device\Device;
use App\Repositories\Device\DeviceRepository;
use Illuminate\Http\JsonResponse;


class DeviceAPIController extends Controller
{
    public function __construct(private readonly DeviceRepository $deviceRepository)
    {}

    public function index(ReadDeviceAPIRequest $request): JsonResponse
    {
        $devices = $request->has('unpaginated') ?
            $this->deviceRepository->all() :
            $this->deviceRepository->paginate(20);

        return $this->makeResponseResource('Devices retrieved Successfully', DeviceResource::collection($devices));

    }

    public function store(CreateDeviceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $device = $this->deviceRepository->create($input);

        return $this->makeResponseResource('Device created Successfully', new DeviceResource($device));
    }

    public function show(Device $device, ShowDeviceAPIRequest $request): JsonResponse
    {
        return $this->makeResponseResource('Devices retrieved Successfully', new DeviceResource($device));
    }
    public function update(Device $device,UpdateDeviceAPIRequest $request): JsonResponse
    {
        $input = $request->validated();

        $client = $this->deviceRepository->update($input, $device->id);

        return $this->makeResponseResource('Devices updated Successfully', new DeviceResource($client));
    }
}
