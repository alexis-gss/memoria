<?php

namespace App\Http\Controllers\Bo\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Auth\EmailRequest;
use App\Mails\Auth\ResetPassword;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    /**
     * Show the form to reset user password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function showResetForm(Request $request): \Illuminate\Contracts\View\View
    {
        return view('back.auth.password.reset', ['token' => $request->route()->parameter('token')]);
    }

    /**
     * Check/store the new password and send an email to the user.
     *
     * @param \App\Http\Requests\Bo\Auth\EmailRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(EmailRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $passwordReset = DB::table(config('auth.passwords.users.table'))->where([
                'email' => $request->email,
                'token' => $request->token
            ]);

            // Check if token exist.
            if (!$passwordReset->first()) {
                return redirect()->back()->with('error', trans('Invalid token!'));
            }

            // Check if token is expired.
            if (
                !Carbon::createFromFormat('Y-m-d H:i:s', $passwordReset->first()->created_at)->between(
                    Carbon::now()->subMinutes(config('auth.passwords.users.expire')),
                    Carbon::now()
                )
            ) {
                $passwordReset->delete();
                return redirect()->back()->with('error', trans('Token expired!'));
            }

            // Update user data.
            $userModel                    = User::query()->where('email', $request->email)->first();
            $userModel->password          = $request->password;
            $userModel->email_verified_at = (!is_null($userModel->email_verified_at)) ?
                $userModel->email_verified_at :
                Carbon::now();
            $userModel->save();

            // Notification target user.
            Mail::to($userModel->email)->send(new ResetPassword((object) $userModel));

            $passwordReset->delete();

            return redirect()->route('bo.login')->with('success', trans('auth.reset_password_complete'));
        });
    }
}
