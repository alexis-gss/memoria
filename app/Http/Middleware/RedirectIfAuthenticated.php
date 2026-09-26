<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request                                                         $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param  string|null                                                                      ...$guards
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \RuntimeException If guard is unhandled.
     * @phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
     */
    public function handle(Request $request, \Closure $next, ...$guards): \Symfony\Component\HttpFoundation\Response
    {
        // phpcs:enable phpcs:disable
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                switch ($guard) {
                    case 'frontend':
                        return \redirect()->route('fo.games.index');
                    case 'backend':
                        if (!$request->routeIs('bo.*')) {
                            continue 2;
                        }
                        return \redirect()->route('bo.home');
                    default:
                        throw new \RuntimeException("Unhandled guard `{$guard}` .");
                }
            }
        }
        return $next($request);
    }
}
