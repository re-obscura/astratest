<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | Публичные маршруты (без авторизации) |-------------------------------------------------------------------------- | | POST /api/register — регистрация нового пользователя | POST /api/login    — вход, получение токена | */

Route::post('/register', [AuthController::class , 'register']);
Route::post('/login', [AuthController::class , 'login']);

/* |-------------------------------------------------------------------------- | Защищённые маршруты (auth:sanctum) |-------------------------------------------------------------------------- | | POST   /api/logout       — выход (удаление токена) | GET    /api/tasks         — список задач текущего пользователя | POST   /api/tasks         — создать задачу | PUT    /api/tasks/{task}  — обновить свою задачу | DELETE /api/tasks/{task}  — удалить свою задачу | GET    /api/user          — данные текущего пользователя | */

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class , 'logout']);
    Route::get('/user', [AuthController::class , 'user']);
    Route::apiResource('tasks', TaskController::class)->except(['show']);
});
