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
import { ref } from 'vue';
import { useTasksStore } from '../stores/tasks';
import { extractErrorMessage } from '../utils/errors';

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
        title.value = '';
        description.value = '';
        emit('task-added');
    } catch (e) {
        error.value = extractErrorMessage(e, 'Ошибка при добавлении задачи.');
    } finally {
        loading.value = false;
    }
}
</script>
