<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Enumerate headers which you do not want in your application's responses.
     * Great starting point would be to go check out @Scott_Helme's:
     * https://securityheaders.com/
     *
     * @var array
     */
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     *
     * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
     */
    public function handle(Request $request, Closure $next): Response
    {
        // phpcs:enable
        View::share('nonce', $nonce = Vite::useCspNonce());
        // * For VueJS dev tool to execute
        $jsDev     = config('app.debug') ? "'unsafe-eval' 'unsafe-inline'" : "'nonce-{$nonce}' 'strict-dynamic'";
        $reportUri = Route::has('cspReportUri') ?
            \sprintf(' report-uri %s ;', \parse_url(route('cspReportUri'), \PHP_URL_PATH)) : '';
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains;');
        $cspPolicies = [
            "default-src 'none';",
            "manifest-src 'self';",
            "connect-src 'self' blob:;",
            'img-src * data: blob:;',
            'frame-src ;',
            "media-src 'self';",
            "font-src 'self' data: ;",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;",
            "script-src 'self' {$jsDev} ;",
        ];

        $response->headers->set('Content-Security-Policy', implode(' ', $cspPolicies) . $reportUri);
        $response->headers->set('Feature-Policy', 'fullscreen \'self\';');

        return $response;
    }

    /**
     * Remove unwanted headers.
     */
    private function removeUnwantedHeaders(array $headerList): void
    {
        foreach ($headerList as $header) {
            header_remove($header);
        }
    }
}
