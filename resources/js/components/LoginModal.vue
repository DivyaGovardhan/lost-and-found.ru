<template>
    <Modal :visible="visible" @update:visible="updateVisible">
        <h2>Вход</h2>
        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="login-username">Логин</label>
                <input id="login-username" v-model="form.login" required />
            </div>
            <div class="form-group">
                <label for="login-password">Пароль</label>
                <input type="password" id="login-password" v-model="form.password" required />
            </div>
            <button type="submit" :disabled="loading" class="submit-btn">Войти</button>
            <p v-if="error" style="color:red;">{{ error }}</p>
        </form>
        <div style="margin-top: 1rem;">
            Ещё не зарегистрированы? <a href="#" @click.prevent="$emit('switch-to-register')">Регистрация</a>
        </div>
        <button class="close" @click="updateVisible(false)">×</button>
    </Modal>
</template>

<script setup>
import { reactive, ref } from 'vue';
import Modal from './Modal.vue';
import useAuth from '../composables/useAuth';

const props = defineProps({
    visible: Boolean,
});

const emit = defineEmits(['update:visible', 'switch-to-register']);

const auth = useAuth();

const form = reactive({
    login: '',
    password: '',
});

const loading = ref(false);
const error = ref(null);

function updateVisible(val) {
    emit('update:visible', val);
}

async function submit() {
    error.value = null;
    loading.value = true;
    try {
        await auth.login(form);
        updateVisible(false);
    } catch (e) {
        error.value = auth.state.error || 'Ошибка входа';
    } finally {
        loading.value = false;
    }
}
</script>
