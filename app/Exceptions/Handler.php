<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $this->renderable(function (Throwable $e, Request $request) {
            if (! $this->wantsSpaJson($request)) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return spa('Errors/404', [
                    'message' => 'Запись не найдена',
                ], 404);
            }

            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();

                if (! in_array($statusCode, [403, 404, 419, 500], true)) {
                    return null;
                }

                $component = match ($statusCode) {
                    403 => 'Errors/403',
                    404 => 'Errors/404',
                    419 => 'Errors/419',
                    default => 'Errors/500',
                };

                return spa($component, [
                    'message' => $e->getMessage(),
                ], $statusCode);
            }

            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return spa('Errors/419', [
                    'message' => 'Срок действия страницы истёк. Обновите её и попробуйте снова.',
                ], 419);
            }

            if ($e instanceof \Illuminate\Database\QueryException) {
                $message = 'Произошла ошибка при работе с базой данных.';

                if (str_contains($e->getMessage(), 'Base table or view not found')) {
                    $message = 'Таблица в базе данных не найдена. Обратитесь к администратору.';
                } elseif (str_contains($e->getMessage(), 'Duplicate entry')) {
                    $message = 'Запись с такими данными уже существует.';
                } elseif (str_contains($e->getMessage(), 'Access denied')) {
                    $message = 'Нет доступа к базе данных. Обратитесь к администратору.';
                }

                return spa('Errors/500', ['message' => $message], 500);
            }

            if ($e instanceof \Exception) {
                \Log::error('SPA error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);

                return spa('Errors/500', [
                    'message' => 'Произошла непредвиденная ошибка. Попробуйте еще раз.',
                ], 500);
            }

            return null;
        });
    }

    private function wantsSpaJson(Request $request): bool
    {
        return $request->header('X-Spa') === '1'
            || $request->is('api')
            || $request->is('api/*')
            || $request->expectsJson();
    }
}
