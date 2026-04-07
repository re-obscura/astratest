<template>
  <!--
    Форма добавления задачи.
    Два поля: title (обязательно) и description (необязательно).
    После успешного добавления — очистка полей + emit('task-added').
  -->
  <form @submit.prevent="handleSubmit" class="task-form">
    <div class="form-row">
      <input
        v-model="title"
        type="text"
        placeholder="Название задачи *"
        class="input-title"
        required
      />
      <input
        v-model="description"
        type="text"
        placeholder="Описание (необязательно)"
        class="input-description"
      />
      <button type="submit" class="btn btn-add" :disabled="loading">
        {{ loading ? '...' : 'Добавить' }}
      </button>
    </div>
    <p v-if="error" class="error-text">{{ error }}</p>
  </form>
</template>

<script setup>
/**
 * TaskForm — компонент формы добавления задачи.
 *
 * При отправке:
 * 1. Вызываем tasksStore.addTask(title, description)
 * 2. Очищаем поля формы
 * 3. Эмитим событие 'task-added' наверх
 *
 * Ошибки валидации показываем под формой.
 */
import { ref } from 'vue';
import { useTasksStore } from '../stores/tasks';

const emit = defineEmits(['task-added']);
const tasksStore = useTasksStore();

const title = ref('');
const description = ref('');
const error = ref('');
const loading = ref(false);

async function handleSubmit() {
    error.value = '';
    loading.value = true;
    try {
        await tasksStore.addTask(title.value, description.value);
        // Очищаем поля после успешного добавления
        title.value = '';
        description.value = '';
        emit('task-added');
    } catch (e) {
        if (e.response && e.response.data) {
            const data = e.response.data;
            if (data.errors && data.errors.title) {
                error.value = data.errors.title[0];
            } else {
                error.value = data.message || 'Ошибка при добавлении задачи.';
            }
        } else {
            error.value = 'Ошибка сети.';
        }
    } finally {
        loading.value = false;
    }
}
</script>
