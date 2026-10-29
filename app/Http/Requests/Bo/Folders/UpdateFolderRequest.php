<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Folders;

use Illuminate\Support\Facades\Gate;

class UpdateFolderRequest extends StoreFolderRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('update', $this->route('folder'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required', 'string', 'min:2', 'max:255',
                // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
                function ($attribute, string $value, $fail): void {
                    /** @var \App\Models\Folder $folderModel */
                    $folderModel = request()->route()->parameter('folder');
                    if (
                        $this->boolean('published')
                        && config('app.locale') !== config('app.fallback_locale')
                        && empty($folderModel->getTranslation('name', config('app.fallback_locale')))
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
