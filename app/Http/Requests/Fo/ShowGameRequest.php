<?php

declare(strict_types=1);

namespace App\Http\Requests\Fo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ShowGameRequest extends FormRequest
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
            'slug' => 'required|string|exists:games,slug',
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
            'slug' => trans('validation.custom.slug'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @throws HttpResponseException HTTP error.
     */
    protected function failedValidation(Validator $validator): void
    {
        abort_if($validator->errors()->has('slug'), Response::HTTP_NOT_FOUND);

        throw new HttpResponseException(
            back()->withErrors($validator)->withInput()
        );
    }
}
