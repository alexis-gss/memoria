<?php

declare(strict_types=1);

namespace App\Http\Requests\Fo;

use Illuminate\Foundation\Http\FormRequest;

class NextPicturesGameRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
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
     * @return array<string,\Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'slug'  => 'required|string|exists:games,slug',
            'page'  => 'required|numeric|min:1',
            'order' => 'required|string|in:likes,date',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string,\Illuminate\Translation\Translator|string|array<mixed>|null>
     */
    public function attributes(): array
    {
        return [
            'slug'  => trans('validation.custom.slug'),
            'page'  => trans('validation.custom.page'),
            'order' => trans('validation.custom.order'),
        ];
    }
}
