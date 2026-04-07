/**
 * Настройка Axios.
 *
 * Создаём один экземпляр axios с общими настройками:
 * - baseURL: '/api' — все запросы идут на /api/...
 * - Accept: application/json — Laravel будет отдавать JSON-ошибки
 *
 * Interceptor на ответ:
 * Если сервер вернул 401 (Unauthenticated) — значит токен
 * невалиден или истёк. Очищаем localStorage и редиректим
 * на страницу логина.
 */
import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
    },
});

// Перед каждым запросом добавляем токен из localStorage
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Если получили 401 — разлогиниваем
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;
