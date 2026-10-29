<?php

declare(strict_types=1);

namespace App\Http\Requests\Bo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChangeOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('backend')->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'direction' => $this->route('direction') === 'up' ? true : false,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'direction' => 'required|boolean',
        ];
    }
}
