<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
        $this->renderable(function (RuntimeException $e, Request $request) {
            return response()->json(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST
            );
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            return response()->json(null, Response::HTTP_UNAUTHORIZED);
        });

        $this->renderable(function (ValidationException $e, Request $request) {
            // todo tutaj foreachem wyrzucić wszystkie błędy i zaznaczyć błędy na formularzu
            return response()->json(
                [
                    'message' => $e->getMessage(),
                    'errors' => $e->validator->errors(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY 
            );
        });

        $this->renderable(function (Throwable $e, Request $request) {
            return response()->json(
                [
                    'message' => 'Server Error',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR 
            );
        });

        $this->reportable(function (RuntimeException $e) {
            return false;
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
