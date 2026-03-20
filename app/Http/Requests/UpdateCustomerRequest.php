<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'       => 'required|string|max:255',
            'last_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:customers,email,' . $this->route('customer'),
            'phone'            => 'required|string|max:20',
            'company'          => 'nullable|string|max:255',
            'address'          => 'nullable|string',
            'status'           => 'nullable|in:active,inactive',
            'assigned_user_id' => 'nullable|exists:users,id',
        ];
    }
}