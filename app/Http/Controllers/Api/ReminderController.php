<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер напоминаний.
 *
 * Два эндпоинта:
 *  - POST   /api/tasks/{task}/reminder  — установить / изменить напоминание
 *  - DELETE /api/tasks/{task}/reminder  — удалить напоминание
 *
 * Бизнес-правила (проверяются в store()):
 *  1. reminder_at > текущего момента
 *  2. Нельзя установить напоминание для completed-задачи
 *  3. updated_at обновляется при изменении reminder_at (touch)
 *  6. reminder_at не может быть в прошлом (уже на просроченную)
 *  9. У пользователя не более 3 активных напоминаний одновременно
 */
class ReminderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Установить или изменить напоминание для задачи.
     *
     * POST /api/tasks/{task}/reminder
     * Body: { "reminder_at": "2026-04-16T10:00:00" }
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        // Проверяем, что задача принадлежит текущему пользователю
        $this->authorize('update', $task);

        $validated = $request->validate([
            'reminder_at' => 'required|date|after:now',
        ], [
            'reminder_at.required' => 'Укажите дату и время напоминания.',
            'reminder_at.date' => 'Неверный формат даты.',
            'reminder_at.after' => 'Напоминание должно быть установлено в будущем (позже текущего момента).',
        ]);

        $reminderAt = \Carbon\Carbon::parse($validated['reminder_at']);

        // Правило 2: нельзя установить напоминание для выполненной задачи
        if ($task->status === 'completed') {
            return response()->json([
                'message' => 'Нельзя установить напоминание для выполненной задачи.',
            ], 422);
        }

        // Правило 9: у пользователя не более 3 активных напоминаний
        // Считаем задачи с reminder_at, исключая текущую
        $activeRemindersCount = $request->user()
            ->tasks()
            ->whereNotNull('reminder_at')
            ->where('id', '!=', $task->id)
            ->count();

        if ($activeRemindersCount >= 3) {
            return response()->json([
                'message' => 'Нельзя иметь более 3 активных напоминаний одновременно.',
            ], 422);
        }

        // Сохраняем напоминание; updated_at обновится автоматически (Rule 3)
        $task->reminder_at = $reminderAt;
        $task->save();

        return response()->json(new TaskResource($task));
    }

    /**
     * Удалить напоминание для задачи.
     *
     * DELETE /api/tasks/{task}/reminder
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        // Проверяем, что задача принадлежит текущему пользователю
        $this->authorize('update', $task);

        $task->reminder_at = null;
        $task->save(); // updated_at обновится автоматически (Rule 3)

        return response()->json(new TaskResource($task));
    }
}
