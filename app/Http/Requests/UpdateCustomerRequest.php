<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $assignedUserRules = [
            Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'sales')),
        ];

        if ($this->user()?->hasRole('sales')) {
            array_unshift($assignedUserRules, 'nullable');
        } else {
            array_unshift($assignedUserRules, 'required');
        }

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email')->ignore($customer?->id),
            ],
            'phone' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'assigned_user_id' => $assignedUserRules,
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_user_id.exists' => 'Assigned user must be a Sales Staff account.',
        ];
    }
}
