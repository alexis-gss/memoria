<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Tags;

use Illuminate\Support\Facades\Gate;

class UpdateTagRequest extends StoreTagRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('update', $this->route('tag'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required', 'string', 'min:2', 'max:25',
                // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
                function ($attribute, string $value, $fail): void {
                    /** @var \App\Models\Tag $tagModel */
                    $tagModel = request()->route()->parameter('tag');
                    if (
                        $this->boolean('published')
                        && config('app.locale') !== config('app.fallback_locale')
                        && empty($tagModel->getTranslation('name', config('app.fallback_locale')))
                    ) {
                        $fail(trans('crud.messages.translation_default_required', [
                            'fallbackLocale' => config('app.fallback_locale'),
                        ]));
                    }
                },
            ],
        ];

        return \array_merge(parent::rules(), $rules);
    }
}
