<?php

namespace App\Http\Requests\Fo;

use Illuminate\Foundation\Http\FormRequest;

class NextPicturesGameRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->route('slug'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'slug' => 'required|string|exists:games,slug',
            'page' => 'required|numeric|min:1',
            'sort' => 'required|string|in:likes,date',
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
            'page' => trans('validation.custom.page'),
            'sort' => trans('validation.custom.sort'),
        ];
    }
}
