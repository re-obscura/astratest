<template>
  <!--
    Главная страница — список задач.
    Состоит из:
    1. Хэдер (имя пользователя + кнопка «Выйти»)
    2. Форма добавления задачи (TaskForm)
    3. Список задач (TaskCard для каждой)
  -->
  <div class="tasks-page">
    <!-- Хэдер -->
    <header class="tasks-header">
      <div class="header-left">
        <h1>Мои задачи</h1>
        <span class="user-name" v-if="authStore.user">
          {{ authStore.user.name }}
        </span>
      </div>
      <button @click="handleLogout" class="btn btn-logout">Выйти</button>
    </header>

    <!-- Форма добавления -->
    <TaskForm @task-added="onTaskAdded" />

    <!-- Индикатор загрузки -->
    <div v-if="tasksStore.loading" class="loading">Загрузка задач...</div>

    <!-- Пустой список -->
    <div v-else-if="tasksStore.tasks.length === 0" class="empty-state">
      <p>Задач пока нет. Добавьте первую!</p>
    </div>

    <!-- Список задач -->
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
/**
 * TasksPage — главная страница задач.
 *
 * При монтировании:
 * 1. Загружаем данные пользователя (fetchUser) — чтобы показать имя
 * 2. Загружаем задачи (fetchTasks)
 *
 * onTaskAdded: колбэк от TaskForm, вызывается после добавления.
 * Задача уже добавлена в store, так что ничего дополнительно делать не нужно.
 */
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

    try {
        await authStore.fetchUser();
    } catch (e) {
        // Если не удалось — авторизация невалидна
    }
    await tasksStore.fetchTasks();

    // Проверка напоминаний каждые 10 секунд
    checkReminders(); // Сразу при загрузке
    reminderInterval = setInterval(checkReminders, 10000);
});

onUnmounted(() => {
    if (reminderInterval) {
        clearInterval(reminderInterval);
    }
});

async function handleLogout() {
    await authStore.logout();
    router.push('/login');
}

function onTaskAdded() {
    // Задача уже в store, ничего делать не нужно
}
</script>
