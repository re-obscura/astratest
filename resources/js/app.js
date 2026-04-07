/**
 * Точка входа Vue-приложения.
 *
 * Порядок инициализации:
 * 1. createApp(App) — создаём корневой экземпляр Vue
 * 2. use(createPinia()) — подключаем Pinia (state management)
 * 3. use(router) — подключаем Vue Router (SPA-навигация)
 * 4. mount('#app') — монтируем в div#app из blade-шаблона
 */
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import '../css/app.css';

const app = createApp(App);

app.use(createPinia());
app.use(router);

app.mount('#app');
