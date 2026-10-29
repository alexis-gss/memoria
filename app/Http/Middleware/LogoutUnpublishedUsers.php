<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutUnpublishedUsers
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userModel = Auth::user();
        if (! is_null($userModel) && ! $userModel->published) {
            Auth::logout();

            return to_route('bo.login')->with('warning', trans('auth.unpublished_user'));
        }

        return $next($request);
    }
}
