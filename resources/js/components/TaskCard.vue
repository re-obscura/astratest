<template>
  <div class="task-card" :class="{ 'task-completed': task.status === 'completed', 'task-overdue': isOverdue }">

    <!-- Режим просмотра -->
    <template v-if="!editing">
      <div class="task-content">
        <h3 class="task-title" @click="startEditing" title="Нажмите для редактирования">
          {{ task.title }}
        </h3>
        <p class="task-description" v-if="task.description">
          {{ truncatedDescription }}
        </p>

        <!-- Напоминание: отображение -->
        <div v-if="task.reminder_at" class="task-reminder">
          Напоминание: {{ formattedReminder }}
        </div>

        <!-- Кнопка напоминания (только для pending) -->
        <div v-if="task.status === 'pending'" class="reminder-toggle">
          <button
            @click="toggleReminderForm"
            class="btn btn-reminder"
            type="button"
          >
            {{ showReminderForm ? 'Скрыть' : (task.reminder_at ? 'Изменить напоминание' : 'Напомнить') }}
          </button>
        </div>

        <!-- Форма напоминания -->
        <div v-if="showReminderForm" class="reminder-form">
          <input
            v-model="reminderInput"
            type="datetime-local"
            class="reminder-dt-input"
          />
          <p v-if="reminderError" class="error-text">{{ reminderError }}</p>
          <div class="reminder-actions">
            <button
              @click="saveReminder"
              class="btn btn-save"
              :disabled="reminderLoading"
              type="button"
            >
              {{ reminderLoading ? '...' : 'Сохранить напоминание' }}
            </button>
            <button
              v-if="task.reminder_at"
              @click="removeReminder"
              class="btn btn-reminder-delete"
              :disabled="reminderLoading"
              type="button"
            >
              Удалить напоминание
            </button>
          </div>
        </div>
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
import { ref, computed } from 'vue';
import { useTasksStore } from '../stores/tasks';
import { extractErrorMessage } from '../utils/errors';
import { formatDatetime, toDatetimeLocalValue } from '../utils/date';
import { useNow } from '../utils/useNow';

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

// Форматирование даты напоминания — computed, не пересчитывается при каждом render
const formattedReminder = computed(() => formatDatetime(props.task.reminder_at));

// Проверка на просроченность — используем общий реактивный now из useNow,
// чтобы не создавать отдельный setInterval на каждую карточку.
const { now } = useNow();

const isOverdue = computed(() => {
    if (props.task.status === 'completed' || !props.task.reminder_at) return false;
    return new Date(props.task.reminder_at) <= now.value;
});

// — Переключение статуса —
const statusLoading = ref(false);

async function toggleStatus() {
    statusLoading.value = true;
    try {
        const newStatus = props.task.status === 'pending' ? 'completed' : 'pending';
        await tasksStore.updateTask(props.task.id, { status: newStatus });
        if (newStatus === 'completed') {
            showReminderForm.value = false;
        }
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
        editError.value = extractErrorMessage(e, 'Ошибка при сохранении.');
    } finally {
        editLoading.value = false;
    }
}

// — Напоминания —
const showReminderForm = ref(false);
const reminderInput = ref('');
const reminderError = ref('');
const reminderLoading = ref(false);

function toggleReminderForm() {
    showReminderForm.value = !showReminderForm.value;
    if (showReminderForm.value) {
        reminderError.value = '';
        reminderInput.value = toDatetimeLocalValue(props.task.reminder_at);
    }
}

async function saveReminder() {
    if (!reminderInput.value) {
        reminderError.value = 'Укажите дату и время напоминания.';
        return;
    }
    reminderError.value = '';
    reminderLoading.value = true;
    try {
        await tasksStore.setReminder(props.task.id, new Date(reminderInput.value).toISOString());
        showReminderForm.value = false;
    } catch (e) {
        reminderError.value = extractErrorMessage(e, 'Ошибка при сохранении напоминания.');
    } finally {
        reminderLoading.value = false;
    }
}

async function removeReminder() {
    reminderLoading.value = true;
    try {
        await tasksStore.deleteReminder(props.task.id);
        showReminderForm.value = false;
    } catch (e) {
        reminderError.value = extractErrorMessage(e, 'Ошибка при удалении напоминания.');
    } finally {
        reminderLoading.value = false;
    }
}
</script>
