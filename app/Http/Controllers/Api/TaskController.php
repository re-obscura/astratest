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
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $request->user()->tasks()->create($request->validated());

        return response()->json(new TaskResource($task->refresh()), 201);
    }

    /**
     * Обновить задачу (только свою).
     *
     * Авторизация через TaskPolicy.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return response()->json(new TaskResource($task));
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
