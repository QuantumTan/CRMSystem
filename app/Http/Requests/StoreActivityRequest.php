<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'   => 'nullable|exists:customers,id',
            'lead_id'       => 'nullable|exists:leads,id',
            'activity_type' => 'required|in:call,email,meeting,note',
            'description'   => 'required|string',
            'activity_date' => 'required|date',
        ];
    }
}