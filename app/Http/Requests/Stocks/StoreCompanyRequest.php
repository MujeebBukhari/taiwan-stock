<?php

namespace App\Http\Requests\Stocks;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-stocks') ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('symbol')) {
            $this->merge([
                'symbol' => strtoupper(trim((string) $this->input('symbol'))),
            ]);
        }

        if ($this->has('currency')) {
            $this->merge([
                'currency' => strtoupper(trim((string) $this->input('currency'))),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'symbol' => ['required', 'string', 'max:20', 'unique:companies,symbol'],
            'name' => ['required', 'string', 'max:150'],
            'exchange' => ['required', 'string', 'max:20'],
            'sector' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
