<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\DevModeOnly;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /**
         * disable auto-redirect to 'login' route in api path
         * @todo create 401 blade for non-api path later
         */
        $middleware->redirectGuestsTo(fn () => null);

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'devonly' => DevModeOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*'));

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->isDebugDisabled() && $request->is('api/*')) {
                return response()->json([
                    'message' => 'No resources found.',
                    'data' => null,
                ], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->isDebugDisabled() && $request->is('api/*')) {
                return response()->json([
                    'message' => 'Forbidden',
                    'data' => null,
                ], 403);
            }
        });

    })->create();
