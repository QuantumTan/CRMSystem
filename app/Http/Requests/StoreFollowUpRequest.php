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
            'customer_id' => 'nullable|required_without:lead_id|exists:customers,id',
            'lead_id' => 'nullable|required_without:customer_id|exists:leads,id',
            'user_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pending,completed',
        ];
    }
}
