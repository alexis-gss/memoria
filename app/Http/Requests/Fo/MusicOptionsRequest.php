<?php

namespace App\Http\Requests\Fo;

use Illuminate\Foundation\Http\FormRequest;

class MusicOptionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string,\Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'muted'  => 'required|boolean',
            'volume' => 'required|numeric|between:0,1',
            'loop'   => 'required|boolean',
            'speed'  => ['required', 'in:0.5,0.75,1,1.25,1.5,2'],
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
            'muted'  => trans('validation.custom.muted'),
            'volume' => trans('validation.custom.volume'),
            'loop'   => trans('validation.custom.loop'),
            'speed'  => trans('validation.custom.speed'),
        ];
    }
}
