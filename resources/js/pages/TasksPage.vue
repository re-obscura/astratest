<template>
  <div class="tasks-page">
    <header class="tasks-header">
      <div class="header-left">
        <h1>Мои задачи</h1>
        <span class="user-name" v-if="authStore.user">{{ authStore.user.name }}</span>
      </div>
      <button @click="handleLogout" class="btn btn-logout">Выйти</button>
    </header>

    <TaskForm @task-added="() => {}" />

    <div v-if="tasksStore.loading" class="loading">Загрузка задач...</div>
    <div v-else-if="tasksStore.tasks.length === 0" class="empty-state">
      <p>Задач пока нет. Добавьте первую!</p>
    </div>
    <div v-else class="tasks-list">
      <TaskCard
        v-for="task in tasksStore.tasks"
        :key="task.id"
        :task="task"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useTasksStore } from '../stores/tasks';
import TaskForm from '../components/TaskForm.vue';
import TaskCard from '../components/TaskCard.vue';

const router = useRouter();
const authStore = useAuthStore();
const tasksStore = useTasksStore();

// Уведомления о напоминаниях
const notifiedTasks = new Set();
let reminderInterval = null;

function checkReminders() {
    if (!tasksStore.tasks) return;
    const now = new Date();
    tasksStore.tasks.forEach(task => {
        if (task.status === 'pending' && task.reminder_at) {
            const reminderDate = new Date(task.reminder_at);
            if (reminderDate <= now && !notifiedTasks.has(task.id)) {
                notifiedTasks.add(task.id);
                triggerNotification(task);
            }
        }
    });
}

function triggerNotification(task) {
    const title = 'Напоминание!';
    const body = task.title;
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body });
    } else {
        alert(`${title}\n${body}`);
    }
}

onMounted(async () => {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Загружаем пользователя только если он ещё не в сторе
    if (!authStore.user) {
        try {
            await authStore.fetchUser();
        } catch (e) {
            // Токен невалиден — axios interceptor перенаправит на /login
        }
    }

    await tasksStore.fetchTasks();

    checkReminders();
    reminderInterval = setInterval(checkReminders, 10000);
});

onUnmounted(() => {
    clearInterval(reminderInterval);
});

async function handleLogout() {
    await authStore.logout();
    router.push('/login');
}
</script>
