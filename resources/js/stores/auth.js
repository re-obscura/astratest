/**
 * Pinia Store: Auth (аутентификация).
 *
 * Хранит:
 *  - token: строка токена из localStorage
 *  - user:  объект текущего пользователя
 *
 * Методы:
 *  - login(email, password) → POST /api/login
 *  - register(name, email, password) → POST /api/register
 *  - logout() → POST /api/logout
 *  - fetchUser() → GET /api/user
 *
 * Геттер isAuthenticated: true если есть токен.
 *
 * Архитектурное решение:
 * Токен хранится и в Pinia state, и в localStorage.
 * localStorage нужен для persistence между перезагрузками страницы.
 * Pinia нужен для реактивности (навигационные гарды, v-if в шаблонах).
 */
import { defineStore } from 'pinia';
import api from '../axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || null,
        user: JSON.parse(localStorage.getItem('user') || 'null'),
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(email, password) {
            const response = await api.post('/login', { email, password });
            this.setAuth(response.data);
        },

        async register(name, email, password, passwordConfirmation) {
            const response = await api.post('/register', {
                name,
                email,
                password,
                password_confirmation: passwordConfirmation,
            });
            this.setAuth(response.data);
        },

        async logout() {
            try {
                await api.post('/logout');
            } catch (e) {
                // Даже если запрос не прошёл — всё равно очищаем локально
            }
            this.clearAuth();
        },

        async fetchUser() {
            const response = await api.get('/user');
            this.user = response.data;
            localStorage.setItem('user', JSON.stringify(this.user));
        },

        /**
         * Вспомогательный метод: сохраняет токен и пользователя
         * после успешного login/register.
         */
        setAuth(data) {
            this.token = data.token;
            this.user = data.user;
            localStorage.setItem('token', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
        },

        clearAuth() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
        },
    },
});
