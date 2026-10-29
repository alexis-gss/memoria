<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     * @param  string|null  ...$guards
     *
     * @throws RuntimeException If guard is unhandled.
     *
     * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        // phpcs:enable phpcs:disable
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                switch ($guard) {
                    case 'frontend':
                        return to_route('fo.games.index');
                    case 'backend':
                        if (! $request->routeIs('bo.*')) {
                            continue 2;
                        }

                        return to_route('bo.home');
                    default:
                        throw new RuntimeException("Unhandled guard `{$guard}` .");
                }
            }
        }

        return $next($request);
    }
}
