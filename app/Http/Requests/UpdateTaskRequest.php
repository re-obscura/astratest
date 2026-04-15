<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,completed',
            // reminder_at can be explicitly set to null via PUT (to clear it)
            // but only via the dedicated /reminder endpoints for setting
            'reminder_at' => 'sometimes|nullable|date',
        ];
    }
}
