<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Utils\Resources\ResponseUtil;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class UnauthorizedRequestException extends HttpResponseException
{
    public array $hiddenRequestParameters = [
        'password',
        'password_confirmation',
    ];

    public function __construct(string $message, int $code = 404, ?Request $request = null)
    {
        if ($request) {
            $this->logExceptionDetails($message, $request);
        }
        parent::__construct(ResponseUtil::makeError($message, $code));
    }

    protected function logExceptionDetails(string $message, Request $request): void
    {
        Log::error('UnauthorizedRequestException', [
            'message' => $message,
            'uri' => $request->getUri(),
            'method' => $request->method(),
            'payload' => $this->payload($this->input($request)),
            'headers' => $request->headers->all(),
        ]);
    }

    protected function payload(array $payload): array
    {
        return $this->hideParameters(
            $payload,
            $this->hiddenRequestParameters
        );
    }

    /**
     * Hide the given parameters.
     */
    protected function hideParameters(array $data, array $hidden): array
    {
        foreach ($hidden as $parameter) {
            if (Arr::get($data, $parameter)) {
                Arr::set($data, $parameter, '********');
            }
        }

        return $data;
    }

    /**
     * Extract the input from the given request.
     */
    private function input(Request $request): array
    {
        $files = $request->files->all();

        array_walk_recursive($files, function (&$file) {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1000).'KB' : '0',
            ];
        });
        /** @var array $input */
        $input = $request->input();

        return array_replace_recursive($input, $files);
    }
}
