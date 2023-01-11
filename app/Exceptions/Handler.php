<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (TokenMismatchException $e, $request) {
            return $this->response(953);
        });
        $this->renderable(function (AuthenticationException $e, $request) {
            return $this->response(950);
        });
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            return $this->response(951);
        });
        $this->renderable(function (QueryException $e, $request) {
            return $this->extendResponse(990, $e->getMessage());
        });  
        $this->renderable(function (ErrorException $e, $request) {
            return $this->extendResponse(990, $e->getMessage());
        });
    }
}
