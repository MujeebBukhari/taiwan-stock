<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBuyTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! $this->user() || ! $this->user()->isActive()) {
            return false;
        }

        $company = $this->route('company');
        if ($company instanceof Company) {
            if (! $company->is_active && ! $this->user()->isAdmin()) {
                return false;
            }
        } elseif ($company) {
            $companyModel = Company::find($company);
            if ($companyModel && ! $companyModel->is_active && ! $this->user()->isAdmin()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Extract company_id from route parameter if available
        if ($this->route('company')) {
            $company = $this->route('company');
            $companyId = is_object($company) ? $company->id : $company;

            $this->merge([
                'company_id' => $this->input('company_id', $companyId),
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
            'company_id' => [
                'required',
                'exists:companies,id',
                function ($attribute, $value, $fail) {
                    $company = Company::find($value);
                    if ($company && ! $company->is_active && ! $this->user()?->isAdmin()) {
                        $fail('Cannot execute a BUY transaction for an inactive company.');
                    }
                },
            ],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'price' => ['required', 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
