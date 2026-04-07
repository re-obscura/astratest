/**
 * Pinia Store: Tasks (задачи).
 *
 * Хранит массив задач текущего пользователя.
 *
 * Каждый метод обращается к API и обновляет локальный state,
 * чтобы UI обновлялся без перезагрузки страницы:
 *
 *  - fetchTasks()     → GET /api/tasks → заполняет this.tasks
 *  - addTask(data)    → POST /api/tasks → добавляет задачу в начало
 *  - updateTask(id, data) → PUT /api/tasks/{id} → обновляет в массиве
 *  - deleteTask(id)   → DELETE /api/tasks/{id} → удаляет из массива
 */
import { defineStore } from 'pinia';
import api from '../axios';

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        loading: false,
    }),

    actions: {
        async fetchTasks() {
            this.loading = true;
            try {
                const response = await api.get('/tasks');
                this.tasks = response.data;
            } finally {
                this.loading = false;
            }
        },

        async addTask(title, description) {
            const response = await api.post('/tasks', { title, description });
            // Добавляем новую задачу в начало массива (новые сверху)
            this.tasks.unshift(response.data);
        },

        async updateTask(id, data) {
            const response = await api.put(`/tasks/${id}`, data);
            // Находим задачу в массиве и заменяем обновлённой
            const index = this.tasks.findIndex((t) => t.id === id);
            if (index !== -1) {
                this.tasks[index] = response.data;
            }
        },

        async deleteTask(id) {
            await api.delete(`/tasks/${id}`);
            // Убираем задачу из массива
            this.tasks = this.tasks.filter((t) => t.id !== id);
        },
    },
});
