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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_at' => 'nullable|date|after:' . now()->addMinutes(15)->toDateTimeString(),
        ];
    }

    public function messages(): array
    {
        return [
            'reminder_at.after' => 'Напоминание должно быть установлено не ранее чем через 15 минут от текущего момента.',
        ];
    }
}
