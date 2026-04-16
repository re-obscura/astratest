<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Контроллер задач (CRUD).
 *
 * Список задач выбирается через связь $request->user()->tasks(),
 * чтобы пользователь видел только свои задачи.
 * Обновление и удаление защищены через TaskPolicy.
 */
class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Получить все задачи текущего пользователя.
     *
     * latest() — сортировка по created_at desc (новые первыми).
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()->latest()->get();

        return response()->json(TaskResource::collection($tasks));
    }

    /**
     * Создать новую задачу.
     *
     * user_id проставляется автоматически через связь.
     * Если передан reminder_at — проверяем лимит в 3 активных напоминания,
     * чтобы нельзя было обойти его через этот эндпоинт.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Проверяем лимит только если reminder_at передан в запросе.
        if (!empty($data['reminder_at'])) {
            $activeRemindersCount = $request->user()
                ->tasks()
                ->whereNotNull('reminder_at')
                ->where('reminder_at', '>', now())
                ->count();

            if ($activeRemindersCount >= 3) {
                return response()->json([
                    'message' => 'Нельзя иметь более 3 активных напоминаний одновременно.',
                ], 422);
            }
        }

        $task = $request->user()->tasks()->create($data);

        return response()->json(new TaskResource($task->refresh()), 201);
    }

    /**
     * Обновить задачу (только свою).
     *
     * Управление напоминаниями вынесено в ReminderController — здесь
     * reminder_at не принимается (поле отсутствует в UpdateTaskRequest).
     * Исключение: при переводе задачи в 'completed' напоминание сбрасывается.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $data = $request->validated();

        // Если задача отмечается выполненной — сбрасываем напоминание.
        if (isset($data['status']) && $data['status'] === 'completed') {
            $data['reminder_at'] = null;
        }

        $task->update($data);

        return response()->json(new TaskResource($task->refresh()));
    }

    /**
     * Удалить задачу (только свою).
     *
     * Авторизация через TaskPolicy.
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Задача удалена.']);
    }
}
