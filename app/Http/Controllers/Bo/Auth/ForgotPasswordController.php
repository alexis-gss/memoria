<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Auth\LinkEmailRequest;
use App\Mails\Auth\LinkResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Store the token and send an email to the user.
     */
    public function sendResetLinkEmail(LinkEmailRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $fields = $request->validated();
            DB::table(config('auth.passwords.users.table'))->insert([
                'email'      => $fields['email'],
                'token'      => $fields['token'],
                'created_at' => Date::now(),
            ]);

            Mail::to($fields['email'])->send(new LinkResetPassword((object) $fields));

            return back()->with('success', trans('auth.reset_password_email'));
        });
    }
}
