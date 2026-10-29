<?php

declare(strict_types=1);

namespace App\Http\Requests\Fo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateRatingRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'uuid' => $this->cookie('rating-uuid') ?? Str::uuid()->toString(),
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
            'uuid'       => 'required|uuid|string',
            'picture_id' => 'required|numeric|exists:pictures,id',
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
            'uuid'       => trans('validation.custom.uuid'),
            'picture_id' => trans('validation.custom.picture_id'),
        ];
    }
}
