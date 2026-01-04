<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response
    {
        if ($request->is('api/*')) {
            if ($e instanceof ValidationException) {
                return apiResponse(
                    false,
                    'Validation Error',
                    errors: $e->errors(),
                    code: Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            if ($e instanceof ModelNotFoundException) {
                return apiResponse(
                    false,
                    'Resource not found',
                    code: Response::HTTP_NOT_FOUND
                );
            }
            if ($e instanceof AuthenticationException) {
                return apiResponse(
                    false,
                    'Unauthorized',
                    code: Response::HTTP_UNAUTHORIZED
                );
            }
            if ($e instanceof AuthorizationException) {
                return apiResponse(
                    false,
                    'Unauthorized',
                    code: Response::HTTP_UNAUTHORIZED
                );
            }
            if ($e instanceof \ErrorException) {
                return apiResponse(
                    false,
                    $e->getMessage() ?? 'Error',
                    code: Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
            if ($e instanceof \Exception) {
                return apiResponse(
                    false,
                    $e->getMessage() ?? 'Error',
                    code: Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
            return apiResponse(
                false,
                $e->getMessage() ?? 'Error',
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return parent::render($request, $e);
    }
}
