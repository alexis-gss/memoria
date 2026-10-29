<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo\Ranks;

use App\Models\Rank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreRankRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('update', Rank::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ranks' => collect($this->ranks)->map(fn ($rank) => intval($rank['id']))->all(),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ranks'   => 'present|array|min:1',
            'ranks.*' => 'required|numeric|exists:games,id|distinct',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'ranks'   => trans('models.rank'),
            'ranks.*' => trans(':field :inter:model', [
                'field' => trans('validation.custom.element'),
                'inter' => trans('validation.custom.inter.male'),
                'model' => trans('models.rank'),
            ]),
        ];
    }
}
