<?php

namespace App\Http\Controllers\Bo\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Auth\LinkEmailRequest;
use App\Mails\Auth\LinkResetPassword;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Store the token and send an email to the user.
     *
     * @param \App\Http\Requests\Bo\Auth\LinkEmailRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(LinkEmailRequest $request): \Illuminate\Http\RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $fields = $request->validated();
            DB::table(config('auth.passwords.users.table'))->insert([
                'email'      => $fields['email'],
                'token'      => $fields['token'],
                'created_at' => Carbon::now()
            ]);

            Mail::to($fields['email'])->send(new LinkResetPassword((object) $fields));

            return redirect()->back()->with('success', trans('auth.reset_password_email'));
        });
    }
}
