<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        $followUp = $this->route('followUp');

        return $followUp && ($this->user()?->can('update', $followUp) ?? false);
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|required_without:lead_id|exists:customers,id',
            'lead_id' => 'nullable|required_without:customer_id|exists:leads,id',
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
                $validator->errors()->add('customer_id', 'Select either a customer or a lead, not both.');
                $validator->errors()->add('lead_id', 'Select either a lead or a customer, not both.');
            }
        }];
    }
}
