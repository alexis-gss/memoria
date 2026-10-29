<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Tags;

use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('create', Tag::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'published' => $this->boolean('published'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name'      => [
                'required', 'string', 'min:2', 'max:25',
                // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
                function ($attribute, string $value, $fail): void {
                    if (
                        $this->boolean('published') && config('app.locale') !== config('app.fallback_locale')
                    ) {
                        $fail(trans('crud.messages.translation_default_required', [
                            'fallbackLocale' => config('app.fallback_locale'),
                        ]));
                    }
                },
            ],
            'published' => 'required|boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'      => trans('validation.attributes.name'),
            'published' => trans('validation.custom.publishment'),
        ];
    }
}
