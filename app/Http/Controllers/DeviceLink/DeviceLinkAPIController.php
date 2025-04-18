<?php

namespace App\Http\Controllers\DeviceLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientDevice\ClientDeviceAPIRequest;
use App\Repositories\LinkDevice\LinkDeviceRepository;
use Illuminate\Http\Request;

class DeviceLinkAPIController extends Controller
{

    public function __construct(private readonly LinkDeviceRepository $linkDeviceRepository)
    {}
    public function __invoke(ClientDeviceAPIRequest $request)
    {
         $this->linkDeviceRepository->all();
    }
}
