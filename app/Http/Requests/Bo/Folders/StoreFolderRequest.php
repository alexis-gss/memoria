<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Folders;

use App\Models\Folder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('update', Folder::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mandatory' => $this->boolean('mandatory'),
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
                'required', 'string', 'min:2', 'max:255',
                // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
                function ($attribute, string $value, $fail): void {
                    if ($this->boolean('published') && config('app.locale') !== config('app.fallback_locale')) {
                        $fail(trans('crud.messages.translation_default_required', [
                            'fallbackLocale' => config('app.fallback_locale'),
                        ]));
                    }
                },
            ],
            'mandatory' => 'required|boolean',
            'published' => 'required|boolean',
            'color'     => 'required|hex_color',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'      => trans('validation.attributes.name'),
            'mandatory' => trans('validation.custom.folder_mandatory'),
            'color'     => trans('validation.custom.color'),
            'published' => trans('validation.custom.publishment'),
        ];
    }
}
