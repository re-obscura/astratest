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
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useTasksStore } from '../stores/tasks';
import TaskForm from '../components/TaskForm.vue';
import TaskCard from '../components/TaskCard.vue';

const router = useRouter();
const authStore = useAuthStore();
const tasksStore = useTasksStore();

onMounted(async () => {
    try {
        await authStore.fetchUser();
    } catch (e) {
        // Если не удалось — авторизация невалидна
    }
    await tasksStore.fetchTasks();
});

async function handleLogout() {
    await authStore.logout();
    router.push('/login');
}

function onTaskAdded() {
    // Задача уже в store, ничего делать не нужно
}
</script>
