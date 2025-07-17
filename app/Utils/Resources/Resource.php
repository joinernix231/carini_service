<?php

declare(strict_types=1);

namespace App\Utils\Resources;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Testing\Fluent\AssertableJson;
use InvalidArgumentException;

abstract class Resource extends JsonResource
{
    public function toJsonAssertion(bool $wrapped = true): Closure
    {
        return function (AssertableJson $json) use ($wrapped) {
            $content = $wrapped ? (string) $this->response()->getContent() : $this->toJson();
            $expectedData = json_decode($content, true);

            if (!is_array($expectedData)) {
                // This is impossible to trigger without writing a broken resource
                // @codeCoverageIgnoreStart
                throw new InvalidArgumentException('Invalid data returned from resource');
                // @codeCoverageIgnoreEnd
            }

            $json->whereAll($expectedData);
        };
    }

    public static function collection($resource): AnonymousResourceCollection
    {
        return new class($resource, static::class) extends AnonymousResourceCollection
        {
            public function toArray($request): array
            {
                /** @var LengthAwarePaginator<Model>|Collection $paginator */
                $paginator = $this->resource;

                if ($paginator instanceof Collection) {
                    return $this->collection->toArray();
                }

                return [
                    'current_page' => $paginator->currentPage(),
                    'data' => $this->collection,
                    'first_page_url' => $paginator->url(1),
                    'from' => $paginator->firstItem(),
                    'last_page' => $paginator->lastPage(),
                    'last_page_url' => $paginator->url($paginator->lastPage()),
                    'next_page_url' => $paginator->nextPageUrl(),
                    'path' => $paginator->path(),
                    'per_page' => $paginator->perPage(),
                    'prev_page_url' => $paginator->previousPageUrl(),
                    'to' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ];
            }

            public function paginationInformation(Request $request, array $paginated, array $default): array
            {
                return [];
            }
        };
    }
}
