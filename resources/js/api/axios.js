import axios from 'axios';

const api = axios.create({
    baseURL: '/api', // если у вас API доступен по этому префиксу
    headers: {
        'Accept': 'application/json',
    },
});

// Добавим interceptor для автоматической подстановки токена
api.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
