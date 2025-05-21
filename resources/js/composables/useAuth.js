import { reactive, readonly } from 'vue';
import api from '../api/axios';

const state = reactive({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
    error: null,
});

async function register(formData) {
    state.loading = true;
    state.error = null;
    try {
        const response = await api.post('/register', formData);
        state.user = response.data.user;
        state.token = response.data.token;
        localStorage.setItem('auth_token', state.token);
    } catch (err) {
        state.error = err.response?.data?.message || 'Ошибка регистрации';
        throw err; // <- Важно пробросить ошибку дальше
    } finally {
        state.loading = false;
    }
}


async function login(formData) {
    state.loading = true;
    state.error = null;
    try {
        const response = await api.post('/login', formData);
        state.user = response.data.user;
        state.token = response.data.token;
        localStorage.setItem('auth_token', state.token);
    } catch (err) {
        state.error = err.response?.data?.message || 'Ошибка авторизации';
    } finally {
        state.loading = false;
    }
}

async function logout() {
    state.loading = true;
    state.error = null;
    try {
        await api.post('/logout');
    } catch {
        // игнорируем ошибки выхода
    }
    state.user = null;
    state.token = null;
    localStorage.removeItem('auth_token');
    state.loading = false;
}

function setUser(user) {
    state.user = user;
}

export default function useAuth() {
    return {
        state: readonly(state),
        register,
        login,
        logout,
        setUser,
    };
}
