<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    public function makeResponseResource(string $message,  $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ]);
    }

    public function makeError(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ],404);
    }

    public function makeResponse(string $message, array|null $data): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ]);
    }
}
