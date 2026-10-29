<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

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
        $this->reportable(function (Throwable $e): void {
            if (app()->bound('sentry')) {
                resolve('sentry')->captureException($e);
            }
        });
    }

    /**
     * Change the path of the render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws Throwable If errors.
     *
     * @phpcs:disable Squiz.Commenting.FunctionComment.TypeHintMissing
     */
    public function render($request, Throwable $exception): Response
    {
        // phpcs:enable
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();

            return response()->view("errors.pages.{$statusCode}", [], $statusCode);
        }

        return parent::render($request, $exception);
    }
}
