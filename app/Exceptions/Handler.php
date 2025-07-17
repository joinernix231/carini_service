<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Utils\Resources\ResponseUtil;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e): JsonResponse|Response
    {

        return match (true) {
            $e instanceof MethodNotAllowedHttpException, $e instanceof NotFoundHttpException => $this->handleNotFoundException($request),
            $e instanceof QueryException => $this->handleQueryException(),
            $e instanceof AuthenticationException => $this->unauthenticated($request, $e),
            $e instanceof ModelNotFoundException => $this->modelNotFoundException(),
            default => parent::render($request, $e),
        };

    }


    protected function handleNotFoundException(Request $request): JsonResponse
    {
        return ResponseUtil::makeError('La ruta '.$request->url().' con el método '.$request->method().', no fue encontrada');
    }

    protected function handleQueryException(): JsonResponse
    {
        return ResponseUtil::makeError('Existe un error con la base de datos', 500);
    }

    protected function unauthenticated($request, AuthenticationException $e): JsonResponse
    {
        return ResponseUtil::makeError('Este usuario no tiene permiso para realizar esta acción', 401);
    }

    protected function modelNotFoundException(): JsonResponse
    {
        return ResponseUtil::makeError('No se encontró el recurso solicitado', 400);
    }
}
