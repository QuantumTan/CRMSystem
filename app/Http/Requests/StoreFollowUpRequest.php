<?php

namespace App\Http\Requests;

use App\Models\FollowUp;
use Illuminate\Foundation\Http\FormRequest;

class StoreFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', FollowUp::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'required|exists:leads,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'nullable|in:pending,completed',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', 'pending'),
        ]);
    }

    public function after(): array
    {
        return [function ($validator): void {
            if ($this->filled('customer_id') && $this->filled('lead_id')) {
                $validator->errors()->add('customer_id', 'New follow-ups should be linked to a lead only.');
                $validator->errors()->add('lead_id', 'Select a lead only when creating a new follow-up.');
            }
        }];
    }
}
