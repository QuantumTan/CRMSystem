<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,proposal_sent,negotiation,won,lost',
            'priority' => 'required|in:low,medium,high,critical',
            'expected_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'assigned_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'sales')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_user_id.exists' => 'Assigned user must be a Sales Staff account.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->filled('status') ? $this->input('status') : config('crm.default_lead_status', 'new'),
            'priority' => $this->filled('priority') ? $this->input('priority') : config('crm.default_lead_priority', 'medium'),
        ]);
    }
}
