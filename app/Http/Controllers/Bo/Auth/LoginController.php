<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:backend')->except('logout');
    }

    /**
     * Get the guard to be used during authentication.
     */
    protected function guard(): StatefulGuard
    {
        return Auth::guard('backend');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm(): View
    {
        return view('back.auth.login');
    }

    /**
     * Where to redirect users after login.
     */
    protected function redirectTo(): string
    {
        $targetUrl = response()->redirectToIntended()->getTargetUrl();

        return ($targetUrl !== config('app.url')) ? $targetUrl : route('bo.home');
    }

    /**
     * Attempt to log the user into the application.
     */
    protected function attemptLogin(Request $request): bool
    {
        $credentials = $this->credentials($request);
        // * Attempt login only using published users.
        $credentials['published'] = true;

        return $this->guard()->attempt(
            $credentials,
            $request->boolean('remember')
        );
    }
}
