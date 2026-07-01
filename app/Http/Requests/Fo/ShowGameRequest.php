<?php

namespace App\Http\Requests\Fo;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ShowGameRequest extends FormRequest
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
            'slug' => ['required', 'string', 'exists:games,slug'],
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

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException HTTP error.
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->has('slug')) {
            abort(Response::HTTP_NOT_FOUND);
        }

        throw new HttpResponseException(
            redirect()->back()->withErrors($validator)->withInput()
        );
    }
}
