<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Utils\ResponseUtil;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Si la petición es una API o espera JSON (como Postman)
        if ($request->expectsJson() || $request->is('api/*')) {
            return match (true) {
                $e instanceof QueryException => $this->handleQueryException(),
                $e instanceof AuthenticationException => $this->unauthenticated($request, $e),
                $e instanceof AuthorizationException => $this->unauthorized(),
                $e instanceof ModelNotFoundException => $this->modelNotFoundException(),
                $e instanceof NotFoundHttpException => $this->notFound(),
                $e instanceof MethodNotAllowedHttpException => $this->methodNotAllowed(),
                $e instanceof UnauthorizedRequestException => $this->customUnauthorized($e),
                $e instanceof HttpException => $this->httpException($e),
                default => $this->internalError($e),
            };
        }

        return parent::render($request, $e);
    }

    protected function unauthenticated($request, AuthenticationException $e): array
    {
        return ResponseUtil::makeError('Este usuario no tiene permiso para realizar esta acción', 401);
    }

    protected function unauthorized(): array
    {
        return ResponseUtil::makeError('Acceso no autorizado', 403);
    }

    protected function handleQueryException(): array
    {
        return ResponseUtil::makeError('Existe un error con la base de datos', 500);
    }

    protected function modelNotFoundException(): array
    {
        return ResponseUtil::makeError('No se encontró el recurso solicitado', 404);
    }

    protected function notFound(): array
    {
        return ResponseUtil::makeError('Ruta no encontrada', 404);
    }

    protected function methodNotAllowed(): array
    {
        return ResponseUtil::makeError('Método HTTP no permitido para esta ruta', 405);
    }

    protected function customUnauthorized(UnauthorizedRequestException $e): array
    {
        return ResponseUtil::makeError($e->getMessage(), $e->getCode() ?: 401);
    }

    protected function httpException(HttpException $e): array
    {
        return ResponseUtil::makeError($e->getMessage(), $e->getStatusCode());
    }

    protected function internalError(Throwable $e): array
    {
        return ResponseUtil::makeError('Error interno del servidor: ' . $e->getMessage(), 500);
    }
}
