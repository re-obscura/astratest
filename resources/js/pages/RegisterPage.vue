<template>
  <!--
    Страница регистрации.
    Форма: name + email + password.
    После успешной регистрации — редирект на /tasks.
  -->
  <div class="auth-page">
    <div class="auth-card">
      <h1 class="auth-title">Регистрация</h1>

      <form @submit.prevent="handleRegister" class="auth-form">
        <div class="form-group">
          <label for="name">Имя</label>
          <input
            id="name"
            v-model="name"
            type="text"
            placeholder="Ваше имя"
            required
          />
        </div>

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
          {{ loading ? 'Регистрация...' : 'Зарегистрироваться' }}
        </button>
      </form>

      <p class="auth-link">
        Уже есть аккаунт?
        <router-link to="/login">Войти</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const name = ref('');
const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

async function handleRegister() {
    error.value = '';
    loading.value = true;
    try {
        await authStore.register(name.value, email.value, password.value);
        router.push('/tasks');
    } catch (e) {
        if (e.response && e.response.data) {
            const data = e.response.data;
            // Ошибки валидации Laravel: { errors: { email: ["The email has already been taken."] } }
            if (data.errors) {
                const firstError = Object.values(data.errors)[0];
                error.value = firstError[0];
            } else {
                error.value = data.message || 'Ошибка регистрации.';
            }
        } else {
            error.value = 'Ошибка сети. Попробуйте позже.';
        }
    } finally {
        loading.value = false;
    }
}
</script>
