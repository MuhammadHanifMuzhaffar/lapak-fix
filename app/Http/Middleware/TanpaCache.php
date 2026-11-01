<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TanpaCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $respons = $next($request);
        $respons->headers->set('Cache-Control', 'no-store');

        return $respons;
    }
}
