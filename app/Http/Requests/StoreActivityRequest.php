<?php

namespace App\Http\Requests;

use App\Models\Activity;
use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Activity::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'in:call,email,meeting,note'],
            'description' => ['required', 'string', 'max:2000'],
            'activity_date' => ['required', 'date'],

            'customer_id' => [
                'nullable',
                'required_without:lead_id',
                'exists:customers,id',
            ],

            'lead_id' => [
                'nullable',
                'required_without:customer_id',
                'exists:leads,id',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('customer_id') && $this->filled('lead_id')) {
                $validator->errors()->add('customer_id', 'Select either a customer or a lead, not both.');
                $validator->errors()->add('lead_id', 'Select either a lead or a customer, not both.');
            }
        });
    }
}