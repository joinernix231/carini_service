<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Other\LoadImageAPIRequest;
use App\Utils\ResourceService;
use Illuminate\Http\JsonResponse;

class ResourceAPIController extends Controller
{
    private ResourceService $resourceService;

    function __construct(ResourceService $resourceService)
    {
        $this->resourceService = $resourceService;
    }

    public function loadImage(LoadImageAPIRequest $request): JsonResponse
    {
        $response = $this->resourceService->uploadImage($request->get('name'),
            $request->get('image'));


        if ($response['status'])
        return $this->makeResponse('Image loaded successfully', null);

        return $this->makeError($response['message']);
    }
}
