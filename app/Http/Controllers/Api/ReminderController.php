<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер напоминаний.
 *
 * POST   /api/tasks/{task}/reminder — установить / изменить напоминание
 * DELETE /api/tasks/{task}/reminder — удалить напоминание
 */
class ReminderController extends Controller
{
    use AuthorizesRequests;

    /**
     * Установить или изменить напоминание для задачи.
     */
    public function store(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        // Нельзя установить напоминание для выполненной задачи
        if ($task->status === 'completed') {
            return response()->json([
                'message' => 'Нельзя установить напоминание для выполненной задачи.',
            ], 422);
        }

        $validated = $request->validate([
            'reminder_at' => 'required|date|after:now',
        ], [
            'reminder_at.required' => 'Укажите дату и время напоминания.',
            'reminder_at.date' => 'Неверный формат даты.',
            'reminder_at.after' => 'Напоминание должно быть установлено в будущем (позже текущего момента).',
        ]);

        // У пользователя не более 3 активных напоминаний (исключая текущую задачу).
        // Считаем только будущие напоминания — просроченные слот не занимают.
        $activeRemindersCount = $request->user()
            ->tasks()
            ->whereNotNull('reminder_at')
            ->where('reminder_at', '>', now())
            ->where('id', '!=', $task->id)
            ->count();

        if ($activeRemindersCount >= 3) {
            return response()->json([
                'message' => 'Нельзя иметь более 3 активных напоминаний одновременно.',
            ], 422);
        }

        $task->reminder_at = Carbon::parse($validated['reminder_at']);
        // Принудительно помечаем модель «грязной», чтобы updated_at обновился
        // даже если значение reminder_at совпадает с предыдущим.
        $task->updated_at = now();
        $task->save();

        return response()->json(new TaskResource($task));
    }

    /**
     * Удалить напоминание для задачи.
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->reminder_at = null;
        $task->save();

        return response()->json(new TaskResource($task));
    }
}
