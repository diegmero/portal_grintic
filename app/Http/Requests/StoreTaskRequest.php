<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'priority' => ['nullable', 'string'],
            'has_subtasks' => ['nullable', 'boolean'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
