<template>
    <div class="container">
        <section class="search-section">
            <img src="../../img/promo.svg" alt="Все находки в одном месте" style="width: 700px; height: 350px;" />

            <div class="search-box">
                <input type="text" v-model="searchQuery" placeholder="Введите ключевое слово для поиска" @keyup.enter="searchPosts" />
                <button @click="searchPosts">Найти</button>
            </div>

            <div class="filters">
                <div class="filter-item">
                    <select v-model="filters.district_ID" @change="fetchPosts">
                        <option value="">Все районы</option>
                        <option v-for="d in districts" :key="d.ID" :value="d.ID">{{ d.name }}</option>
                    </select>
                </div>

                <div class="filter-item">
                    <select v-model="filters.category_ID" @change="fetchPosts">
                        <option value="">Все категории</option>
                        <option v-for="c in categories" :key="c.ID" :value="c.ID">{{ c.name }}</option>
                    </select>
                </div>

                <div class="filter-item">
                    <select v-model="filters.found_ID" @change="fetchPosts">
                        <option value="">Все статусы</option>
                        <option v-for="status in foundStatuses" :key="status.ID" :value="status.ID">{{ status.name }}</option>
                    </select>
                </div>
            </div>
        </section>

        <section class="">
            <div v-if="loading">Загрузка...</div>
            <div v-else class="loading-container">
                <div v-if="posts.length === 0">Объявления не найдены</div>
                <div v-else class="items-list">
                    <div v-for="post in posts" :key="post.ID" class="item-card">
                        <div class="item-image">
                            <img :src="'/storage/' + post.photo || '../../img/default.png'" alt="Фото предмета" />
                            <span :class="['status-flag', post.foundStatus?.name.toLowerCase()]">{{ post.foundStatus?.name }}</span>
                        </div>
                        <a href="#" @click.prevent="$emit('open-post', post.id)">{{ post.title }}</a>
                        <div class="date-views">
                            <p class="date">{{ formatDate(post.date) }}</p>
                        </div>
                        <p class="address">{{ formatAddress(post) }}</p>
                        <p class="description">{{ post.description }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';

const posts = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const filters = ref({
    district_ID: '',
    category_ID: '',
    found_ID: '',
});

const categories = ref([]);
const districts = ref([]);
const foundStatuses = ref([]);

// Форматируем дату в дд.мм.гг
function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('ru-RU');
}

// Формируем адрес из данных поста
function formatAddress(post) {
    let parts = [];
    if (post.district?.name) parts.push(post.district.name);
    if (post.street) parts.push(post.street);
    if (post.house) parts.push(post.house);
    return parts.join(', ');
}

// Открытие поста — можно реализовать переход или модалку
function openPost(id) {
    alert('Открыть объявление с ID: ' + id);
}

async function fetchCategories() {
    try {
        const { data } = await api.get('/categories');
        categories.value = data;
    } catch (e) {
        console.error('Ошибка загрузки категорий', e);
    }
}

async function fetchDistricts() {
    try {
        const { data } = await api.get('/districts');
        districts.value = data;
    } catch (e) {
        console.error('Ошибка загрузки районов', e);
    }
}

async function fetchFoundStatuses() {
    try {
        const { data } = await api.get('/found-statuses'); // Предполагается, что есть такой API
        foundStatuses.value = data;
    } catch (e) {
        foundStatuses.value = [
            { ID: 1, name: 'Найдено' },
            { ID: 2, name: 'Потеряно' },
        ];
    }
}

function attachFoundStatusToPosts() {
    posts.value.forEach(post => {
        post.foundStatus = foundStatuses.value.find(status => status.ID === post.found_ID) || null;
    });
}

async function fetchPosts() {
    loading.value = true;
    try {
        const params = {};
        if (filters.value.district_ID) params.district_ID = filters.value.district_ID;
        if (filters.value.category_ID) params.category_ID = filters.value.category_ID;
        if (filters.value.found_ID) params.found_ID = filters.value.found_ID;

        let data;
        if (searchQuery.value.trim().length >= 2) {
            const response = await api.get('/posts/search', { params: { query: searchQuery.value.trim() } });
            data = response.data.data || response.data;
        } else {
            const response = await api.get('/posts', { params });
            data = response.data.data || response.data;
        }

        posts.value = data;
        attachFoundStatusToPosts();  // <-- добавляем здесь
    } catch (e) {
        console.error('Ошибка загрузки объявлений', e);
        posts.value = [];
    } finally {
        loading.value = false;
    }
}

function searchPosts() {
    fetchPosts();
}

onMounted(async () => {
    await Promise.all([fetchCategories(), fetchDistricts(), fetchFoundStatuses()]);
    fetchPosts();
});
</script>
<style scoped>
.status-flag.потеряно {
    background-color: #e74c3c; /* красный */
}

.status-flag.найдено {
    background-color: #27ae60; /* зеленый */
}

</style>
