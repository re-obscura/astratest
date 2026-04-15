/**
 * Pinia Store: Tasks (задачи).
 *
 * Хранит массив задач текущего пользователя.
 *
 * Методы:
 *  - fetchTasks()          → GET  /api/tasks
 *  - addTask(data)         → POST /api/tasks
 *  - updateTask(id, data)  → PUT  /api/tasks/{id}
 *  - deleteTask(id)        → DELETE /api/tasks/{id}
 *  - setReminder(id, dt)   → POST /api/tasks/{id}/reminder
 *  - deleteReminder(id)    → DELETE /api/tasks/{id}/reminder
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
            this.tasks.unshift(response.data);
        },

        async updateTask(id, data) {
            const response = await api.put(`/tasks/${id}`, data);
            const index = this.tasks.findIndex((t) => t.id === id);
            if (index !== -1) {
                this.tasks[index] = response.data;
            }
        },

        async deleteTask(id) {
            await api.delete(`/tasks/${id}`);
            this.tasks = this.tasks.filter((t) => t.id !== id);
        },

        /**
         * Установить или изменить напоминание.
         * @param {number} id - ID задачи
         * @param {string} reminderAt - ISO datetime строка
         */
        async setReminder(id, reminderAt) {
            const response = await api.post(`/tasks/${id}/reminder`, { reminder_at: reminderAt });
            const index = this.tasks.findIndex((t) => t.id === id);
            if (index !== -1) {
                this.tasks[index] = response.data;
            }
        },

        /**
         * Удалить напоминание.
         * @param {number} id - ID задачи
         */
        async deleteReminder(id) {
            const response = await api.delete(`/tasks/${id}/reminder`);
            const index = this.tasks.findIndex((t) => t.id === id);
            if (index !== -1) {
                this.tasks[index] = response.data;
            }
        },
    },
});
