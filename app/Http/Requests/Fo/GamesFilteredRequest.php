<?php

namespace App\Http\Requests\Fo;

use Illuminate\Foundation\Http\FormRequest;

class GamesFilteredRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'filters_id'   => 'nullable|array',
            'filters_id.0' => 'nullable|string|exists:tags,slug',
            'filters_id.1' => 'nullable|string|exists:folders,slug',
            'search'       => 'nullable|string|max:255',
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
            'filters_id' => trans('validation.custom.filters_id'),
            'search'     => trans('validation.custom.search'),
        ];
    }
}
