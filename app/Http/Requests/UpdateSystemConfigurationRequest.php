<?php

namespace App\Http\Requests;

use App\Services\LeadService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:150'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'default_lead_status' => ['required', Rule::in(LeadService::STATUS_OPTIONS)],
            'default_lead_priority' => ['required', Rule::in(LeadService::PRIORITY_OPTIONS)],
            'currency_code' => ['required', 'string', 'max:10'],
            'password_reset_expire_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'app_name' => trim((string) $this->input('app_name')),
            'company_email' => $this->filled('company_email') ? trim((string) $this->input('company_email')) : null,
            'company_phone' => $this->filled('company_phone') ? trim((string) $this->input('company_phone')) : null,
            'company_address' => $this->filled('company_address') ? trim((string) $this->input('company_address')) : null,
            'currency_code' => strtoupper(trim((string) $this->input('currency_code', 'PHP'))),
        ]);
    }
}
