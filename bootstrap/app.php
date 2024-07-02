<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->appendToGroup('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
     
        $middleware->prependToGroup('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            return response()->json(
                [
                    'message' => 'Brak obiektu',
                ],
                Response::HTTP_BAD_REQUEST
            );
        });

        $exceptions->renderable(function (RuntimeException $e, Request $request) {
            return response()->json(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        });

        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->renderable(function (ValidationException $e, Request $request) {
            return response()->json(
                [
                    'message' => $e->getMessage(),
                    'errors' => $e->validator->errors(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY 
            );
        });

        $exceptions->renderable(function (Throwable $e, Request $request) {
            return response()->json(
                [
                    'message' => 'Server Error',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR 
            );
        });

        $exceptions->reportable(function (RuntimeException $e) {
            return false;
        });

        $exceptions->reportable(function (Throwable $e) {
            //
        });
    })->create();
