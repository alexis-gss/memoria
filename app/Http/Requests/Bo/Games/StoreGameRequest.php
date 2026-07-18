<?php

namespace App\Http\Requests\Bo\Games;

use App\Models\Game;
use App\Traits\Requests\HasPicture;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class StoreGameRequest extends FormRequest
{
    use HasPicture;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        return Gate::check('update', Game::class);
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug'      => Str::of(strip_tags($this->name))->slug()->value(),
            'published' => $this->boolean('published'),
        ]);
        $this->mergePicture();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'slug'        => 'required|string|unique:games,slug|max:255',
            'name'        => 'required|string|min:3|max:255',
            'folder_id'   => 'required|integer|exists:folders,id',
            'akora_id'    => 'required|integer|min:0',
            'published'   => 'required|boolean',
            'music'       => 'nullable|file|mimes:mp3,mpga|max:10240|unique:games,music',
            'tags'        => 'sometimes|array',
            'tags.*'      => 'required|array',
            'tags.*.id'   => 'required|numeric|exists:tags,id|distinct',
        ];
        return \array_merge($rules, $this->pictureRules(minWidth: 400, minHeight: 225, maxWidth: 400, maxHeight: 225));
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'slug'        => trans('validation.custom.slug'),
            'name'        => trans('validation.attributes.name'),
            'picture'     => trans('validation.attributes.image'),
            'folder_id'   => trans('validation.custom.folder_associated'),
            'akora_id'    => trans('validation.custom.akora_associated'),
            'published'   => trans('validation.custom.publishment'),
            'music'       => trans('validation.custom.music'),
            'tags'        => trans('models.tag'),
            'tags.*'      => trans('models.tag'),
            'tags.*.id'   => trans(':field :inter:model', [
                'field' => trans('validation.custom.identification'),
                'inter' => trans('validation.custom.inter.vowel'),
                'model' => trans('models.tag'),
            ]),
        ];
    }
}
