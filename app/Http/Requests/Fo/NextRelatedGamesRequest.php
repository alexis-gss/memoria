<?php

namespace App\Http\Requests\Fo;

use Illuminate\Foundation\Http\FormRequest;

class NextRelatedGamesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,\Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug' => 'required|string|exists:games,slug',
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
            'slug' => trans('validation.custom.slug'),
        ];
    }
}
