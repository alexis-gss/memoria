<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Auth;

use Illuminate\Foundation\Http\FormRequest;

class EmailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'token'                 => 'required|string|size:64',
            'email'                 => 'required|email:rfc,strict,dns,spoof,filter|exists:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'token'                 => trans('validation.attributes.token'),
            'email'                 => trans('validation.attributes.email'),
            'password'              => trans('validation.attributes.password'),
            'password_confirmation' => trans('validation.attributes.password_confirmation'),
        ];
    }
}
