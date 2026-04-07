/**
 * Vue Router — навигация SPA.
 *
 * Маршруты:
 *  /login    — страница входа (гости)
 *  /register — страница регистрации (гости)
 *  /tasks    — главная страница задач (авторизованные)
 *  /         — редирект на /tasks
 *
 * Navigation Guard (beforeEach):
 *  - Если маршрут требует auth (meta.requiresAuth) и нет токена
 *    в localStorage → redirect на /login.
 *  - Если маршрут для гостей (meta.guest) и есть токен
 *    → redirect на /tasks (залогиненному нечего делать на /login).
 */
import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../pages/LoginPage.vue';
import RegisterPage from '../pages/RegisterPage.vue';
import TasksPage from '../pages/TasksPage.vue';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: LoginPage,
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: RegisterPage,
        meta: { guest: true },
    },
    {
        path: '/tasks',
        name: 'tasks',
        component: TasksPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/',
        redirect: '/tasks',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');

    if (to.meta.requiresAuth && !token) {
        // Не авторизован → на логин
        next({ name: 'login' });
    } else if (to.meta.guest && token) {
        // Уже залогинен → на задачи
        next({ name: 'tasks' });
    } else {
        next();
    }
});

export default router;
