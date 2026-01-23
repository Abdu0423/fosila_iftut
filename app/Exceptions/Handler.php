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

        $this->renderable(function (Throwable $e, $request) {
            if ($request->header('X-Inertia')) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    // Для Inertia запросов используем back() с ошибками
                    // Inertia автоматически обработает это как частичное обновление
                    // без полной перезагрузки страницы
                    // Для POST запросов back() вернет ответ с ошибками без редиректа
                    return back()
                        ->withErrors($e->errors())
                        ->withInput($request->except('password'));
                }
                
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    $statusCode = $e->getStatusCode();
                    
                    if ($statusCode === 403) {
                        return \Inertia\Inertia::render('Errors/403', [
                            'message' => $e->getMessage()
                        ])->toResponse($request)->setStatusCode(403);
                    }
                    
                    if ($statusCode === 404) {
                        return \Inertia\Inertia::render('Errors/404')->toResponse($request)->setStatusCode(404);
                    }
                }
                
                // Обработка ошибок базы данных
                if ($e instanceof \Illuminate\Database\QueryException) {
                    $message = 'Произошла ошибка при работе с базой данных.';
                    
                    // Специальные сообщения для разных типов ошибок
                    if (str_contains($e->getMessage(), 'Base table or view not found')) {
                        $message = 'Таблица в базе данных не найдена. Обратитесь к администратору.';
                    } elseif (str_contains($e->getMessage(), 'Duplicate entry')) {
                        $message = 'Запись с такими данными уже существует.';
                    } elseif (str_contains($e->getMessage(), 'Access denied')) {
                        $message = 'Нет доступа к базе данных. Обратитесь к администратору.';
                    }
                    
                    return back()->with('error', $message);
                }
                
                // Обработка других исключений
                if ($e instanceof \Exception) {
                    $message = 'Произошла непредвиденная ошибка. Попробуйте еще раз.';
                    
                    // Логируем ошибку для отладки
                    \Log::error('Inertia error', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return \Inertia\Inertia::render('Errors/500', [
                        'message' => $message
                    ])->toResponse($request)->setStatusCode(500);
                }
                
                // Fallback для неизвестных ошибок
                return \Inertia\Inertia::render('Errors/500', [
                    'message' => 'Произошла ошибка. Попробуйте еще раз.'
                ])->toResponse($request)->setStatusCode(500);
            }
        });
    }
}
