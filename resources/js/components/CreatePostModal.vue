<template>
    <Modal :visible="visible" @update:visible="updateVisible">
        <h2 class="modal-title">Создать новое объявление</h2>
        <form @submit.prevent="submit" class="post-form">
            <div class="form-group">
                <label>Заголовок</label>
                <input v-model="form.title" class="input" required />
                <span v-if="errors.title" class="error">{{ errors.title[0] }}</span>
            </div>
            <div class="form-group">
                <label>Описание</label>
                <textarea v-model="form.description" class="input" required></textarea>
                <span v-if="errors.description" class="error">{{ errors.description[0] }}</span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Категория</label>
                    <select v-model="form.category_id" class="input" required>
                        <option disabled value="">Категория</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <span v-if="errors.category_id" class="error">{{ errors.category_id[0] }}</span>
                </div>
                <div class="form-group">
                    <label>Район</label>
                    <select v-model="form.district_id" class="input" required>
                        <option disabled value="">Район</option>
                        <option v-for="d in districts" :key="d.id" :value="d.id">{{ d.name }}</option>
                    </select>
                    <span v-if="errors.district_id" class="error">{{ errors.district_id[0] }}</span>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Улица</label>
                    <input v-model="form.street" class="input" required />
                    <span v-if="errors.street" class="error">{{ errors.street[0] }}</span>
                </div>
                <div class="form-group">
                    <label>Дом</label>
                    <input v-model="form.house" class="input" required />
                    <span v-if="errors.house" class="error">{{ errors.house[0] }}</span>
                </div>
            </div>
            <div class="form-group radio-group">
                <label>
                    <input type="radio" value="found" v-model="form.status" required />
                    Найдено
                </label>
                <label>
                    <input type="radio" value="lost" v-model="form.status" required />
                    Потеряно
                </label>
                <span v-if="errors.status" class="error">{{ errors.status[0] }}</span>
            </div>
            <div class="form-group photo-upload">
                <label class="photo-label">
                    <img src="../../img/photoFolder.svg" alt="Добавить фото" class="photo-icon" />
                    <span>Добавить фото</span>
                    <input type="file" @change="onFileChange" accept="image/*" style="display:none" />
                </label>
                <div v-if="form.photoName" class="photo-name">{{ form.photoName }}</div>
                <span v-if="errors.photo" class="error">{{ errors.photo[0] }}</span>
            </div>
            <button class="submit-btn" :disabled="loading">Сохранить объявление</button>
            <ul v-if="generalError" class="error-list">
                <li>{{ generalError }}</li>
            </ul>
        </form>
        <button class="close" @click="updateVisible(false)">×</button>
    </Modal>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import Modal from './Modal.vue';
import api from '../api/axios';

const props = defineProps({
    visible: Boolean,
});
const emit = defineEmits(['update:visible']);

const updateVisible = (val) => emit('update:visible', val);

const form = reactive({
    title: '',
    description: '',
    category_id: '',
    district_id: '',
    street: '',
    house: '',
    status: '',
    photo: null,
    photoName: '',
});

const errors = reactive({});
const generalError = ref('');
const loading = ref(false);

const categories = ref([]);
const districts = ref([]);

// Получить категории и районы при открытии модалки
onMounted(async () => {
    categories.value = await fetchCategories();
    districts.value = await fetchDistricts();
});

async function fetchCategories() {
    try {
        const response = await api.get('/categories');
        // Преобразуем, если нужно, чтобы id соответствовал ID из БД
        return response.data.map(cat => ({ id: cat.ID, name: cat.name }));
    } catch (error) {
        console.error('Ошибка загрузки категорий', error);
        return [];
    }
}

async function fetchDistricts() {
    try {
        const response = await api.get('/districts');
        return response.data.map(district => ({ id: district.ID, name: district.name }));
    } catch (error) {
        console.error('Ошибка загрузки районов', error);
        return [];
    }
}


function onFileChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        form.photoName = file.name;
    }
}

async function submit() {
    loading.value = true;
    generalError.value = '';
    Object.keys(errors).forEach(key => errors[key] = null);

    const foundMap = {
        found: 1,
        lost: 2,
    };

    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('description', form.description);
    formData.append('category_ID', form.category_id);
    formData.append('district_ID', form.district_id);
    formData.append('street', form.street || '');
    formData.append('house', form.house || '');
    formData.append('found_ID', foundMap[form.status]);
    if (form.photo) formData.append('photo', form.photo);

    try {
        await api.post('/posts', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        updateVisible(false);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            const respErrors = e.response.data.errors;
            Object.assign(errors, respErrors);
        } else {
            generalError.value = e.response?.data?.message || 'Ошибка при создании объявления';
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
/* Используйте ваши классы и стили из шаблона */
.modal-title {
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
}
.post-form {
    /* ваш стиль формы */
}
.form-group {
    margin-bottom: 1rem;
}
.input {
    width: 100%;
    border-radius: 8px;
    border: 1.5px solid #e0e0e0;
    padding: 8px;
    font-size: 16px;
}
.form-row {
    display: flex;
    gap: 1rem;
}
.radio-group {
    display: flex;
    gap: 2rem;
    align-items: center;
}
.photo-upload {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}
.photo-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}
.photo-icon {
    width: 32px;
    height: 32px;
}
.photo-name {
    font-size: 14px;
    color: #333;
    margin-top: 0.5rem;
}
.submit-btn {
    background: #FC443A;
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 2rem;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 1rem;
}
.error {
    color: #FC443A;
    font-size: 13px;
}
.error-list {
    color: #FC443A;
    margin-top: 1rem;
    padding-left: 1rem;
}
</style>
