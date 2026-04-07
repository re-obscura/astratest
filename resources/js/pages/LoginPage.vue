<template>
  <!--
    Страница логина.
    Форма: email + password.
    При отправке вызываем authStore.login().
    Ошибки показываем красным текстом под формой.
  -->
  <div class="auth-page">
    <div class="auth-card">
      <h1 class="auth-title">Вход</h1>

      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="you@example.com"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Пароль</label>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="Минимум 6 символов"
            required
          />
        </div>

        <p v-if="error" class="error-text">{{ error }}</p>

        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Входим...' : 'Войти' }}
        </button>
      </form>

      <p class="auth-link">
        Нет аккаунта?
        <router-link to="/register">Зарегистрироваться</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
/**
 * LoginPage — страница входа.
 *
 * Логика:
 * 1. Пользователь вводит email и password.
 * 2. При submit вызывается authStore.login().
 * 3. Если успешно — router.push('/tasks').
 * 4. Если ошибка — показываем текст ошибки.
 *
 * Ошибки от сервера приходят в error.response.data:
 *  - 401: { message: "Неверный email или пароль." }
 *  - 422: { message: "...", errors: { email: [...] } }
 */
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

async function handleLogin() {
    error.value = '';
    loading.value = true;
    try {
        await authStore.login(email.value, password.value);
        router.push('/tasks');
    } catch (e) {
        if (e.response && e.response.data) {
            // Сервер вернул ошибку
            error.value = e.response.data.message || 'Ошибка входа.';
        } else {
            error.value = 'Ошибка сети. Попробуйте позже.';
        }
    } finally {
        loading.value = false;
    }
}
</script>
