<template>
    <Modal :visible="visible" @update:visible="updateVisible">
        <h2>Редактировать объявление</h2>
        <form @submit.prevent="submit">
            <div class="form-group">
                <label>Заголовок</label>
                <input v-model="form.title" required />
                <span v-if="errors.title" class="error">{{ errors.title[0] }}</span>
            </div>

            <div class="form-group">
                <label>Описание</label>
                <textarea v-model="form.description" required></textarea>
                <span v-if="errors.description" class="error">{{ errors.description[0] }}</span>
            </div>

            <div class="form-group">
                <label>Категория</label>
                <select v-model="form.category_ID" required>
                    <option v-for="cat in categories" :key="cat.ID" :value="cat.ID">{{ cat.name }}</option>
                </select>
                <span v-if="errors.category_ID" class="error">{{ errors.category_ID[0] }}</span>
            </div>

            <div class="form-group">
                <label>Район</label>
                <select v-model="form.district_ID" required>
                    <option v-for="dist in districts" :key="dist.ID" :value="dist.ID">{{ dist.name }}</option>
                </select>
                <span v-if="errors.district_ID" class="error">{{ errors.district_ID[0] }}</span>
            </div>

            <div class="form-group">
                <label>Улица</label>
                <input v-model="form.street" />
                <span v-if="errors.street" class="error">{{ errors.street[0] }}</span>
            </div>

            <div class="form-group">
                <label>Дом</label>
                <input v-model="form.house" />
                <span v-if="errors.house" class="error">{{ errors.house[0] }}</span>
            </div>

            <div class="form-group">
                <label>Статус</label>
                <select v-model="form.found_ID" required>
                    <option v-for="status in foundStatuses" :key="status.ID" :value="status.ID">{{ status.name }}</option>
                </select>
                <span v-if="errors.found_ID" class="error">{{ errors.found_ID[0] }}</span>
            </div>

            <div class="form-group">
                <label>Фото</label>
                <input type="file" @change="onFileChange" accept="image/*" />
                <div v-if="form.photoName">Выбран файл: {{ form.photoName }}</div>
                <span v-if="errors.photo" class="error">{{ errors.photo[0] }}</span>
            </div>

            <button type="submit" :disabled="loading">Сохранить</button>
        </form>
        <button class="close-btn" @click="updateVisible(false)">Закрыть</button>
    </Modal>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import Modal from './Modal.vue';
import api from '../api/axios';

const props = defineProps({
    visible: Boolean,
    postId: [Number, String],
});

const emit = defineEmits(['update:visible', 'updated']);

const form = reactive({
    title: '',
    description: '',
    category_ID: null,
    district_ID: null,
    street: '',
    house: '',
    found_ID: null,
    photo: null,
    photoName: '',
});

const errors = reactive({});
const loading = ref(false);

const categories = ref([]);
const districts = ref([]);
const foundStatuses = ref([]);

async function fetchMeta() {
    try {
        const [catRes, distRes] = await Promise.all([
            api.get('/categories'),
            api.get('/districts'),
        ]);
        categories.value = catRes.data;
        districts.value = distRes.data;
        // Статусы можно захардкодить или получить с API
        foundStatuses.value = [
            { ID: 1, name: 'Найдено' },
            { ID: 2, name: 'Потеряно' },
        ];
    } catch (e) {
        console.error('Ошибка загрузки справочников', e);
    }
}

async function fetchPost() {
    if (!props.postId) return;
    loading.value = true;
    try {
        const { data } = await api.get(`/posts/${props.postId}`);
        form.title = data.title;
        form.description = data.description;
        form.category_ID = data.category_ID;
        form.district_ID = data.district_ID;
        form.street = data.street || '';
        form.house = data.house || '';
        form.found_ID = data.found_ID;
        form.photoName = ''; // Фото меняется только при загрузке нового файла
        form.photo = null;
    } catch (e) {
        alert('Ошибка загрузки объявления');
    } finally {
        loading.value = false;
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
    console.log('Редактируем пост с ID:', props.postId);

    if (!props.postId) {
        alert('Ошибка: не указан ID объявления');
        return;
    }
    loading.value = true;
    Object.keys(errors).forEach(k => (errors[k] = null));
    try {
        const formData = new FormData();
        formData.append('title', form.title);
        formData.append('description', form.description);
        formData.append('category_ID', form.category_ID);
        formData.append('district_ID', form.district_ID);
        formData.append('street', form.street);
        formData.append('house', form.house);
        formData.append('found_ID', form.found_ID);
        if (form.photo) formData.append('photo', form.photo);

        await api.put(`/posts/${props.postId}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        emit('updated');
        updateVisible(false);
    } catch (e) {
        if (e.response && e.response.status === 422) {
            Object.assign(errors, e.response.data.errors);
        } else {
            alert('Ошибка при сохранении');
        }
    } finally {
        loading.value = false;
    }
}

function updateVisible(val) {
    emit('update:visible', val);
}

watch(() => props.visible, (val) => {
    if (val) {
        fetchMeta();
        fetchPost();
    }
});
</script>

<style scoped>
.form-group {
    margin-bottom: 1rem;
}
input, select, textarea {
    width: 100%;
    padding: 0.5rem;
    border-radius: 8px;
    border: 1.5px solid #ccc;
    font-size: 1rem;
}
.error {
    color: #fc443a;
    font-size: 0.85rem;
}
button[type="submit"] {
    background: #fc443a;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
}
.close-btn {
    margin-top: 1rem;
    background: transparent;
    border: none;
    color: #666;
    cursor: pointer;
}
</style>
