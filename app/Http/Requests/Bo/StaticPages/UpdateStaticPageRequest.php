<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\StaticPages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateStaticPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('update', $this->route('static_page'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'seo_title'       => 'required|min:3|max:70',
            'seo_description' => 'required|min:50|max:160',
            'title'           => 'required|min:3|max:255',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'seo_title'       => trans('validation.custom.seo_title'),
            'seo_description' => trans('validation.custom.seo_description'),
            'title'           => trans('validation.attributes.title'),
        ];
    }
}
