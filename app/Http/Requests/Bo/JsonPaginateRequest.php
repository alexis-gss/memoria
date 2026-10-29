<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class JsonPaginateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => intval($this->input('page')),
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
            'search'      => 'sometimes|nullable|string|between:0,255',
            'page'        => 'required|numeric|min:1',
            'targetModel' => [
                'required',
                'string',
                'starts_with:\\App\\Models\\',
                // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
                function ($attribute, $value, $fail): void {
                    if (! class_exists($value)) {
                        $fail("Le modèle spécifié n'existe pas.");

                        return;
                    }
                    if (! is_subclass_of($value, Model::class)) {
                        $fail("Le modèle spécifié n'est pas un modèle Eloquent.");
                    }
                },
            ],
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
            'search'      => trans('validation.custom.search'),
            'targetModel' => trans('validation.custom.targetModel'),
        ];
    }
}
