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
            // reminder_at изменяется только через /api/tasks/{task}/reminder
            // — здесь поле намеренно отсутствует, чтобы его нельзя было
            //   обнулить или подменить через обычный PUT /api/tasks/{task}.
        ];
    }
}
