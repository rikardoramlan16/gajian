<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Paksa root URL mengikuti host request yang sedang diakses.
 *
 * Berguna ketika APP_URL menunjuk domain lain (mis. env produksi) sehingga
 * route(), url(), dan asset() tetap menghasilkan URL yang benar untuk host
 * yang sedang dibuka (mencegah tautan/fetch ke domain yang salah).
 */
class ForceRequestHostUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHttpHost();

        if ($host) {
            URL::forceRootUrl($request->getScheme().'://'.$host);

            if ($request->isSecure()) {
                URL::forceScheme('https');
            } else {
                URL::forceScheme('http');
            }
        }

        return $next($request);
    }
}
