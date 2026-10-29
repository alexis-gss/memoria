<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Auth\EmailRequest;
use App\Mails\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    /**
     * Show the form to reset user password.
     */
    public function showResetForm(Request $request): View
    {
        return view('back.auth.password.reset', ['token' => $request->route()->parameter('token')]);
    }

    /**
     * Check/store the new password and send an email to the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(EmailRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $passwordReset = DB::table(config('auth.passwords.users.table'))->where([
                'email' => $request->email,
                'token' => $request->token,
            ]);

            // Check if token exist.
            if (! $passwordReset->first()) {
                return back()->with('error', trans('Invalid token!'));
            }

            // Check if token is expired.
            if (
                ! Date::createFromFormat('Y-m-d H:i:s', $passwordReset->first()->created_at)->between(
                    Date::now()->subMinutes(config('auth.passwords.users.expire')),
                    Date::now(),
                )
            ) {
                $passwordReset->delete();

                return back()->with('error', trans('Token expired!'));
            }

            // Update user data.
            $userModel                    = User::query()->where('email', $request->email)->first();
            $userModel->password          = $request->password;
            $userModel->email_verified_at ??= Date::now();
            $userModel->save();

            // Notification target user.
            Mail::to($userModel->email)->send(new ResetPassword((object) $userModel));

            $passwordReset->delete();

            return to_route('bo.login')->with('success', trans('auth.reset_password_complete'));
        });
    }
}
