<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Users;

use App\Enums\Users\RoleEnum;
use App\Models\User;
use App\Traits\Requests\HasPicture;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    use HasPicture;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('create', User::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'published' => $this->boolean('published'),
        ]);
        $this->mergePicture();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|min:3|max:255',
            'last_name'  => 'required|string|min:3|max:255',
            'email'      => 'required|unique:users,email|email:rfc,strict,dns,spoof,filter|max:255',
            'role'       => ['required', new Enum(RoleEnum::class)],
            'password'   => [
                'required', 'required_with:password_confirmation', 'confirmed', 'max:255',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'published'  => 'required|boolean',
        ];

        return \array_merge($rules, $this->pictureRules(minWidth: 100, minHeight: 100, maxWidth: 100, maxHeight: 100));
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name'             => trans('validation.attributes.first_name'),
            'last_name'              => trans('validation.attributes.last_name'),
            'email'                  => trans('validation.attributes.email'),
            'role'                   => trans('validation.attributes.role'),
            'picture'                => trans('validation.attributes.image'),
            'password'               => trans('validation.attributes.password'),
            'password.letters'       => trans('validation.password.letters'),
            'password.mixed'         => trans('validation.password.mixed'),
            'password.numbers'       => trans('validation.password.numbers'),
            'password.symbols'       => trans('validation.password.symbols'),
            'password.uncompromised' => trans('validation.password.uncompromised'),
            'published'              => trans('validation.custom.publishment'),
        ];
    }
}
