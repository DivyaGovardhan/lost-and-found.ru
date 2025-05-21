// import { createApp } from 'vue';
// import App from './App.vue';
// import axios from 'axios';
// import '../css/app.css';
//
// // Настройка axios
// axios.defaults.baseURL = '/api';
// axios.defaults.withCredentials = true;
//
// const app = createApp(App);
//
// // Создаем auth и делаем его доступным глобально
// import { useAuth } from './composables/useAuth';
// const auth = useAuth();
//
// // Делаем auth доступным для всех компонентов
// app.provide('auth', auth);
//
// // Загружаем пользователя и монтируем приложение
// auth.loadUserFromToken().finally(() => {
//     app.mount('#app');
// });
import { createApp } from 'vue';
import App from './App.vue';

createApp(App).mount('#app');
