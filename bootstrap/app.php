<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // CSRF exempt routes — webhooks must never require CSRF tokens
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'api/juicyway/webhook',
            'api/paygrid/inflow',
            'api/budpay/webhook',
            'api/korapay/webhook',
        ]);

        // Route middleware aliases
        // Override the default guest middleware to redirect to the correct portal
        // instead of /home (which doesn't exist and causes a 404 loop).
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo(fn ($request) => match(auth()->user()?->user_type) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'teacher', 'teaching_assistant' => route('teacher.dashboard'),
            'accountant'           => route('accountant.dashboard'),
            'parent'               => route('parent.dashboard'),
            default                => route('admin.dashboard'),
        });

        // All middleware aliases in one place
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'force.password'     => \App\Http\Middleware\ForcePasswordChange::class,
            'impersonate'        => \App\Http\Middleware\Impersonate::class,
            'teacher.access' => \App\Http\Middleware\TeacherAccess::class,

        ]);

        // Apply ForcePasswordChange globally to authenticated web routes
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
