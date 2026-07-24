<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DevModeOnly
{
    public const MODE_DEV = 'dev';
    public const MODE_DEBUG = 'debug';

    public function handle(
        Request $request,
        Closure $next,
        string ...$modes,
    ): Response {
        if (in_array(self::MODE_DEV, $modes) && !$this->isDevEnvironment()) {
            throw new NotFoundHttpException(
                'this endpoint is restricted to the local or development environment'
            );
        }

        if (in_array(self::MODE_DEBUG, $modes) && !$this->isDebugMode()) {
            throw new NotFoundHttpException(
                'this endpoint is restricted to debug mode'
            );
        }

        return $next($request);
    }

    protected function isDevEnvironment(): bool
    {
        return app()->environment(['local', 'development']);
    }

    protected function isDebugMode(): bool
    {
        return config('app.debug');
    }
}