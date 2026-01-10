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
            'company_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ];
    }
}
