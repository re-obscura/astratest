<template>
  <!--
    Карточка задачи.

    Два режима:
    1. Просмотр (editing === false):
       - Заголовок (клик → переход в режим редактирования)
       - Описание (обрезано до 50 символов)
       - Кнопка переключения статуса
       - Кнопка удаления

    2. Редактирование (editing === true):
       - Инпуты title/description
       - Кнопки «Сохранить» и «Отмена»
  -->
  <div class="task-card" :class="{ 'task-completed': task.status === 'completed' }">

    <!-- Режим просмотра -->
    <template v-if="!editing">
      <div class="task-content">
        <h3 class="task-title" @click="startEditing" title="Нажмите для редактирования">
          {{ task.title }}
        </h3>
        <p class="task-description" v-if="task.description">
          {{ truncatedDescription }}
        </p>
      </div>

      <div class="task-actions">
        <button
          @click="toggleStatus"
          class="btn btn-status"
          :class="task.status === 'completed' ? 'btn-status-completed' : 'btn-status-pending'"
          :disabled="statusLoading"
        >
          {{ task.status === 'completed' ? '✓ Выполнена' : '○ Активна' }}
        </button>
        <button
          @click="handleDelete"
          class="btn btn-delete"
          :disabled="deleteLoading"
        >
          {{ deleteLoading ? '...' : '✕ Удалить' }}
        </button>
      </div>
    </template>

    <!-- Режим редактирования -->
    <template v-else>
      <form @submit.prevent="saveEdit" class="edit-form">
        <div class="form-group">
          <label>Заголовок</label>
          <input v-model="editTitle" type="text" required />
        </div>
        <div class="form-group">
          <label>Описание</label>
          <textarea v-model="editDescription" rows="3"></textarea>
        </div>
        <p v-if="editError" class="error-text">{{ editError }}</p>
        <div class="edit-actions">
          <button type="submit" class="btn btn-save" :disabled="editLoading">
            {{ editLoading ? 'Сохранение...' : 'Сохранить' }}
          </button>
          <button type="button" @click="cancelEditing" class="btn btn-cancel">
            Отмена
          </button>
        </div>
      </form>
    </template>
  </div>
</template>

<script setup>
/**
 * TaskCard — компонент карточки задачи.
 *
 * Props:
 *  - task: объект задачи { id, title, description, status }
 *
 * Режим просмотра:
 *  - Клик на заголовок → startEditing()
 *  - Кнопка статуса → toggleStatus() → PUT запрос
 *  - Кнопка удаления → handleDelete() → DELETE запрос
 *
 * Режим редактирования:
 *  - Сохранить → saveEdit() → PUT запрос
 *  - Отмена → cancelEditing()
 *
 * truncatedDescription: обрезает описание до 50 символов + '...'
 */
import { ref, computed } from 'vue';
import { useTasksStore } from '../stores/tasks';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
});

const tasksStore = useTasksStore();

// Обрезка описания до 50 символов
const truncatedDescription = computed(() => {
    if (!props.task.description) return '';
    if (props.task.description.length <= 50) return props.task.description;
    return props.task.description.substring(0, 50) + '...';
});

// — Переключение статуса —
const statusLoading = ref(false);

async function toggleStatus() {
    statusLoading.value = true;
    try {
        const newStatus = props.task.status === 'pending' ? 'completed' : 'pending';
        await tasksStore.updateTask(props.task.id, { status: newStatus });
    } finally {
        statusLoading.value = false;
    }
}

// — Удаление —
const deleteLoading = ref(false);

async function handleDelete() {
    deleteLoading.value = true;
    try {
        await tasksStore.deleteTask(props.task.id);
    } finally {
        deleteLoading.value = false;
    }
}

// — Редактирование —
const editing = ref(false);
const editTitle = ref('');
const editDescription = ref('');
const editError = ref('');
const editLoading = ref(false);

function startEditing() {
    editing.value = true;
    editTitle.value = props.task.title;
    editDescription.value = props.task.description || '';
    editError.value = '';
}

function cancelEditing() {
    editing.value = false;
}

async function saveEdit() {
    editError.value = '';
    editLoading.value = true;
    try {
        await tasksStore.updateTask(props.task.id, {
            title: editTitle.value,
            description: editDescription.value,
        });
        editing.value = false;
    } catch (e) {
        if (e.response && e.response.data) {
            const data = e.response.data;
            if (data.errors && data.errors.title) {
                editError.value = data.errors.title[0];
            } else {
                editError.value = data.message || 'Ошибка при сохранении.';
            }
        } else {
            editError.value = 'Ошибка сети.';
        }
    } finally {
        editLoading.value = false;
    }
}
</script>
