<?php

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\RevalidateBackHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Tự động xóa Cache trình duyệt cho toàn bộ nhóm web
        $middleware->web(append: [
            RevalidateBackHistory::class,
            EnsureUserIsActive::class,

        ]);

        // Đăng ký alias middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 419 || $request->expectsJson()) {
                return null; // để Laravel xử lý như mặc định
            }

            // Vẫn còn đăng nhập (chỉ là trang cũ): quay lại trang trước, giữ dữ liệu form
            if ($request->user()) {
                return redirect()->back()
                    ->withInput($request->except(['_token', 'password', 'password_confirmation', 'file']))
                    ->with('error', 'Trang đã hết hạn. Vui lòng thử lại.');
            }

            // Phiên đăng nhập đã hết: về trang đăng nhập
            return redirect()->route('login')
                ->with('error', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
        });
    })->create();
