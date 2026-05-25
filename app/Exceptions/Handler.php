<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
{   
    // ✅ 419 Session Expired Handle
    if ($exception instanceof TokenMismatchException) {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => 'Session expired! Please login again.'
            ], 419);
        }

        return redirect()->route('remittances.login')
            ->with('error', 'Session expired! Please login again.');
    }
    if ($this->isHttpException($exception)) {
        $status = $exception->getStatusCode();
        if (view()->exists("errors.$status")) {
            return response()->view("errors.$status", ['exception' => $exception], $status);
        }
        return response()->view("errors.default", ['exception' => $exception], $status);
    }
    return parent::render($request, $exception);
}


}