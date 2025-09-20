<?php

namespace App\Http\Requests\Bo;

use App\Enums\Theme\BootstrapThemeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ThemeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,\Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', new Enum(BootstrapThemeEnum::class)],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string,\Illuminate\Contracts\Translation\Translator|string|array<mixed>|null>
     */
    public function attributes(): array
    {
        return [
            'theme' => trans('validation.custom.theme'),
        ];
    }
}
