/**
 * Pinia Store: Tasks (задачи).
 *
 * Хранит массив задач текущего пользователя.
 */
import { defineStore } from 'pinia';
import api from '../axios';

export const useTasksStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        loading: false,
    }),

    actions: {
        /**
         * Заменить задачу в массиве по данным из ответа API.
         */
        _replaceTask(id, updatedTask) {
            const index = this.tasks.findIndex((t) => t.id === id);
            if (index !== -1) {
                this.tasks.splice(index, 1, updatedTask);
            }
        },

        async fetchTasks() {
            this.loading = true;
            try {
                const response = await api.get('/tasks');
                this.tasks = response.data;
            } finally {
                this.loading = false;
            }
        },

        async addTask({ title, description }) {
            const response = await api.post('/tasks', { title, description });
            this.tasks.unshift(response.data);
        },

        async updateTask(id, data) {
            const response = await api.put(`/tasks/${id}`, data);
            this._replaceTask(id, response.data);
        },

        async deleteTask(id) {
            await api.delete(`/tasks/${id}`);
            this.tasks = this.tasks.filter((t) => t.id !== id);
        },

        async setReminder(id, reminderAt) {
            const response = await api.post(`/tasks/${id}/reminder`, { reminder_at: reminderAt });
            this._replaceTask(id, response.data);
        },

        async deleteReminder(id) {
            const response = await api.delete(`/tasks/${id}/reminder`);
            this._replaceTask(id, response.data);
        },
    },
});
