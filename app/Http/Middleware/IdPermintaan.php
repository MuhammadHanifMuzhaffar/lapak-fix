<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class IdPermintaan
{
    public const HEADER = 'X-Request-Id';

    public function handle(Request $request, Closure $next): Response
    {
        $id = (string) Str::uuid();

        $request->attributes->set('id_permintaan', $id);
        Log::withContext(['id_permintaan' => $id]);

        $respons = $next($request);
        $respons->headers->set(self::HEADER, $id);

        return $respons;
    }
}
