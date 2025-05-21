<template>
    <Modal :visible="visible" @update:visible="updateVisible">
        <h2>Регистрация</h2>
        <form @submit.prevent="submit">
            <div class="form-group">
                <label for="reg-name">Ваше имя</label>
                <input id="reg-name" v-model="form.name" required />
            </div>
            <div class="form-group">
                <label for="reg-username">Логин</label>
                <input id="reg-username" v-model="form.login" required />
            </div>
            <div class="form-group">
                <label for="reg-password">Пароль</label>
                <input type="password" id="reg-password" v-model="form.password" required />
            </div>
            <div class="form-group">
                <label for="reg-confirm-password">Повторите пароль</label>
                <input type="password" id="reg-confirm-password" v-model="form.password_confirmation" required />
            </div>

            <div class="form-group" style="position: relative;">
                <label for="reg-birth">Дата рождения</label>
                <input type="date" id="reg-birth" v-model="form.birthday" required />
                <img src="../../img/calendar.svg" alt="Календарь" @click="openDatePicker" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; width: 24px; height: 24px;" />
            </div>

            <div class="form-group">
                <label for="reg-phone">Номер телефона</label>
                <input id="reg-phone" v-model="form.phone" placeholder="+7" />
            </div>

            <div class="form-group">
                <label>Ссылки на соцсети <small>(не обязательно)</small></label>
                <div v-for="(social, index) in form.contacts" :key="index" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <input v-model="form.contacts[index]" placeholder="https://..." style="flex: 1;" />
                    <button type="button" @click="removeSocial(index)" title="Удалить" style="background:#ccc;color:#333;border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;">−</button>
                </div>
                <button type="button" @click="addSocial" style="background:#FC443A; color:#fff; border:none; border-radius:50%; width:32px; height:32px; font-size:24px; cursor:pointer;">+</button>
            </div>

            <button type="submit" :disabled="loading" class="submit-btn">Зарегистрироваться</button>
            <p v-if="error" style="color:red;">{{ error }}</p>
        </form>

        <div style="margin-top: 1rem;">
            Уже зарегистрированы? <a href="#" @click.prevent="$emit('switch-to-login')">Войти</a>
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

const emit = defineEmits(['update:visible', 'switch-to-login']);

const auth = useAuth();

const form = reactive({
    name: '',
    login: '',
    password: '',
    password_confirmation: '',
    birthday: '',
    phone: '',
    contacts: [''],
});

const loading = ref(false);
const error = ref(null);

function updateVisible(val) {
    emit('update:visible', val);
}

function addSocial() {
    form.contacts.push('');
}

function removeSocial(index) {
    form.contacts.splice(index, 1);
}

function openDatePicker() {
    const input = document.getElementById('reg-birth');
    if (input && input.showPicker) {
        input.showPicker();
    } else if (input) {
        input.focus();
    }
}

async function submit() {
    error.value = null;
    loading.value = true;

    if (form.password !== form.password_confirmation) {
        error.value = 'Пароли не совпадают';
        loading.value = false;
        return;
    }

    if (!form.phone || form.phone.trim() === '') {
        error.value = 'Номер телефона обязателен';
        loading.value = false;
        return;
    }

    try {
        // Формируем contacts: номер телефона + соцсети (фильтруем пустые)
        const contacts = [form.phone.trim(), ...form.contacts.filter(c => c.trim() !== '')];

        await auth.register({
            name: form.name,
            login: form.login,
            password: form.password,
            password_confirmation: form.password_confirmation,
            birthday: form.birthday,
            contacts,
        });
        updateVisible(false);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            const errors = e.response.data.errors;
            error.value = Object.values(errors).flat().join(' ');
        } else if (e.response && e.response.data && e.response.data.message) {
            error.value = e.response.data.message;
        } else {
            error.value = 'Ошибка регистрации';
        }
    } finally {
        loading.value = false;
    }
}
</script>
