<?php

namespace App\Http\Controllers\DeviceLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientDevice\ClientDeviceAPIRequest;
use App\Repositories\LinkDevice\ClientRepository;
use Illuminate\Http\Request;

class DeviceLinkAPIController extends Controller
{

    public function __construct(private readonly ClientRepository $linkDeviceRepository)
    {}
    public function __invoke(ClientDeviceAPIRequest $request)
    {
         $this->linkDeviceRepository->all();
    }
}
