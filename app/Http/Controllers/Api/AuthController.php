<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
     * Валидация:
     *  - name: обязательно, строка, макс 255
     *  - email: обязательно, email, уникально в таблице users
     *  - password: обязательно, минимум 6 символов
     *
     * После создания пользователя генерируем Sanctum-токен
     * и возвращаем его клиенту.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // createToken('auth_token') — создаёт PersonalAccessToken в БД
        // plainTextToken — строковый токен вида "id|random_string"
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
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
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Неверный email или пароль.',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
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
        return response()->json($request->user());
    }
}
