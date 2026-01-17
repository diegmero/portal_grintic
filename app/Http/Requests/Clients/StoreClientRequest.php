<?php

namespace App\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'], // For update
            'company_name' => ['sometimes', 'required', 'string', 'max:255'], // For store
            'tax_id' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'industry' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive,archived'],
            'contact_name' => ['nullable', 'string', 'max:255'], // specific for store
            'contact_email' => ['nullable', 'email', 'max:255', 'unique:users,email'], // specific for store
        ];
    }
}
