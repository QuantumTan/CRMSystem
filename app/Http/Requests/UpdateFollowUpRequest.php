<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        $followUp = $this->route('followUp');

        return $followUp && ($this->user()?->can('update', $followUp) ?? false);
    }

    public function rules(): array
    {
        $followUp = $this->route('followUp');
        $isCustomerOnlyFollowUp = $followUp && ! $followUp->lead_id && $followUp->customer_id;

        return [
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => [
                'nullable',
                'exists:leads,id',
                Rule::requiredIf(! $isCustomerOnlyFollowUp),
            ],
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,completed',
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($this->filled('customer_id') && $this->filled('lead_id')) {
                $validator->errors()->add('customer_id', 'Keep the follow-up linked to one record only.');
                $validator->errors()->add('lead_id', 'Keep the follow-up linked to one record only.');
            }
        }];
    }
}
