<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Контроллер аутентификации.
 *
 * Три эндпоинта:
 *  - register: создаёт пользователя + возвращает токен
 *  - login:    проверяет credentials + возвращает токен
 *  - logout:   удаляет текущий токен
 *
 * Используем Sanctum token-based auth (не cookie-based SPA),
 * чтобы фронтенд и бэкенд могли быть на разных портах.
 */
class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя.
     *
     * После создания пользователя генерируем Sanctum-токен
     * и возвращаем его клиенту.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Логин (аутентификация).
     *
     * Проверяем email + password через Auth::attempt().
     * Если неуспешно — возвращаем 401.
     * Если успешно — создаём новый Sanctum-токен.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Неверный email или пароль.',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Выход (удаление текущего токена).
     *
     * currentAccessToken() — возвращает токен, с которым
     * пришёл текущий запрос. Удаляем только его,
     * чтобы не разлогинивать другие сессии.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Вы вышли из системы.',
        ]);
    }

    /**
     * Получить данные текущего аутентифицированного пользователя.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()));
    }
}
