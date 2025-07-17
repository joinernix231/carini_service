<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Other\LoadDocAPIRequest;
use App\Http\Requests\API\Other\LoadImageAPIRequest;
use App\Utils\Resources\ResourceService;
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
            $request->file('image'));


        if ($response['status'])
        return $this->makeResponse('Image loaded successfully', null);

        return $this->makeError($response['message']);
    }

    public function loadDoc(LoadDocAPIRequest $request): JsonResponse
    {
        $filename = 'pdfs/' . $request->get('name');

        $response = $this->resourceService->uploadFile($filename, $request->file('file'));

        if ($response['status'])
            return $this->makeResponse('File loaded successfully', null);

        return $this->makeError($response['message']);
    }
}
