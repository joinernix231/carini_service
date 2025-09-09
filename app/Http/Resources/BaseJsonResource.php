<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\MissingValue;

class BaseJsonResource extends JsonResource
{

    public static function collection($resource)
    {
        if ($resource instanceof MissingValue)
            return null;

        $resource = parent::collection($resource);

        if ($resource->resource instanceof Collection)
            return $resource;

        return [
            'current_page' => $resource->currentPage(),
            'data' => $resource,
            'first_page_url' => $resource->url(1),
            'from' => $resource->firstItem(),
            'last_page' => $resource->lastPage(),
            'last_page_url' => $resource->url($resource->lastPage()),
            'next_page_url' => $resource->nextPageUrl(),
            'path' => $resource->path(),
            'per_page' => $resource->perPage(),
            'prev_page_url' => $resource->previousPageUrl(),
            'to' => $resource->lastItem(),
            'total' => $resource->total(),
        ];
    }

}
