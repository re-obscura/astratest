<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Контроллер задач (CRUD).
 *
 * Ключевое архитектурное решение:
 * Все запросы идут через $request->user()->tasks(),
 * а не через Task::find(). Это гарантирует, что пользователь
 * может видеть/редактировать/удалять ТОЛЬКО свои задачи.
 * Если задача принадлежит другому пользователю — findOrFail()
 * вернёт 404.
 */
class TaskController extends Controller
{
    /**
     * Получить все задачи текущего пользователя.
     *
     * Используем связь user->tasks(), поэтому
     * чужие задачи НИКОГДА не попадут в ответ.
     * latest() — сортировка по created_at desc (новые первыми).
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()->latest()->get();

        return response()->json($tasks);
    }

    /**
     * Создать новую задачу.
     *
     * Валидация:
     *  - title: обязательно, строка, макс 255
     *  - description: необязательно, строка
     *
     * Статус по умолчанию — 'pending' (задан в миграции).
     * user_id проставляется автоматически через связь.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // create() через связь автоматически подставит user_id
        $task = $request->user()->tasks()->create($validated);

        return response()->json($task, 201);
    }

    /**
     * Обновить задачу (только свою).
     *
     * findOrFail($id) ищет задачу ТОЛЬКО среди задач
     * текущего пользователя. Если id чужой задачи —
     * автоматически 404.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,completed',
        ]);

        $task = $request->user()->tasks()->findOrFail($id);
        $task->update($validated);

        return response()->json($task);
    }

    /**
     * Удалить задачу (только свою).
     *
     * Та же логика — ищем по связи, чужую не удалим.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Задача удалена.']);
    }
}
