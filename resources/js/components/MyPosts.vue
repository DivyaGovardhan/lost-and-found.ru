<template>
    <div class="container">
        <h2 style="margin-bottom: 2rem;">Мои объявления</h2>

        <div v-if="loading" class="loading">Загрузка...</div>

        <div v-else>
            <div v-if="!selectedPostId">
                <div v-if="posts.length === 0">У вас пока нет объявлений.</div>
                <div v-else>
                    <div v-for="post in posts" :key="post.id" class="user-post-card">
                        <div class="user-post-image">
                            <img :src="post.photo ? `/storage/${post.photo}` : '/img/default.png'" alt="Фото предмета" />
                        </div>
                        <div class="user-post-info">
                            <div class="user-post-meta">
                                <span class="user-post-date">{{ formatDate(post.date) }}</span>
                            </div>
                            <div class="user-post-title">
                                <a href="#" @click.prevent="openPostDetails(post.id)"><b>{{ post.title }}</b></a>
                            </div>
                            <div class="user-post-address"><b>{{ post.district?.name }}</b></div>
                            <div class="user-post-description">{{ truncate(post.description, 200) }}</div>
                            <div class="user-post-actions">
                                <button class="status-btn" @click="toggleStatus(post.id)"
                                        :class="{'active': post.post_status.id === 1, 'inactive': post.post_status.id === 2}">
                                    {{ post.post_status.id === 1 ? 'Активно' : 'Закрыто' }}
                                </button>
                                <button class="edit-btn" @click="openEditModal(post.id)">Редактировать</button>
                                <button class="delete-btn" @click="deletePost(post.id)">Удалить</button>
                            </div>
                        </div>
                    </div>
                    <!-- пагинация -->
                </div>
            </div>

            <PostDetails v-else :postId="selectedPostId" @back="selectedPostId = null" />
        </div>

        <EditPostModal
            :visible="editModalVisible"
            :postId="editPostId"
            @update:visible="val => editModalVisible = val"
            @updated="onPostUpdated"
        />
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';
import EditPostModal from './EditPostModal.vue';
import PostDetails from './PostDetails.vue';

const posts = ref([]);
const loading = ref(true);
const pagination = ref(null);

const editModalVisible = ref(false);
const editPostId = ref(null);

const selectedPostId = ref(null);

function openEditModal(id) {
    editPostId.value = id;
    editModalVisible.value = true;
}

function onPostUpdated() {
    editModalVisible.value = false;
    fetchPosts();
}

function openPostDetails(id) {
    selectedPostId.value = id;
}

async function fetchPosts(page = 1) {
    loading.value = true;
    try {
        const { data } = await api.get('/user/posts', { params: { page } });
        posts.value = data.posts.data || data.posts;
        if (data.posts.current_page) {
            pagination.value = {
                current_page: data.posts.current_page,
                last_page: data.posts.last_page,
            };
        }
    } catch (e) {
        posts.value = [];
    } finally {
        loading.value = false;
    }
}

async function toggleStatus(id) {
    try {
        const { data } = await api.post(`/posts/${id}/toggle-status`);
        // Находим пост в массиве и обновляем его статус
        const postIndex = posts.value.findIndex(p => p.id === id);
        if (postIndex !== -1) {
            posts.value[postIndex].post_status = data.post.post_status;
        }
        alert(data.message);
    } catch (e) {
        alert('Ошибка при изменении статуса: ' + (e.response?.data?.message || e.message));
    }
}

async function deletePost(id) {
    if (!confirm('Вы уверены, что хотите удалить объявление?')) return;

    try {
        await api.delete(`/posts/${id}`);
        alert('Объявление удалено');
        fetchPosts();
    } catch (e) {
        alert('Ошибка при удалении');
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('ru-RU');
}

function truncate(str, n) {
    if (!str) return '';
    return str.length > n ? str.slice(0, n) + ' ...' : str;
}

onMounted(() => {
    fetchPosts();
});
</script>

<style scoped>
.user-post-card {
    display: flex;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px #0001;
    margin-bottom: 2rem;
    padding: 1.5rem;
    align-items: flex-start;
    gap: 2rem;
}

.user-post-image {
    position: relative;
    width: 160px;
    height: 160px;
    flex-shrink: 0;
    background: #f3f3f3;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-post-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 15px;
}

.status-flag {
    position: absolute;
    top: 12px;
    left: 12px;
    padding: 6px 16px;
    border-radius: 8px;
    color: #fff;
    font-weight: bold;
    font-size: 1rem;
}
.status-flag.found {
    background: #ff9800;
}
.status-flag.lost {
    background: #F44336;
}

.user-post-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}

.user-post-meta {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    font-size: 1rem;
    color: #888;
}

.user-post-title {
    font-size: 1.2rem;
    margin: .5rem 0;
}

.user-post-address {
    font-weight: 600;
    margin-bottom: .5rem;
}

.user-post-description {
    color: #444;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.user-post-actions {
    display: flex;
    gap: 1rem;
}

.edit-btn {
    background: #4CAF50;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .5rem 1.5rem;
    font-size: 1rem;
    cursor: pointer;
}

.delete-btn {
    background: #F44336;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: .5rem 1.5rem;
    font-size: 1rem;
    cursor: pointer;
}

.eye-icon {
    width: 20px;
    height: 20px;
    margin-left: 6px;
}

.loading {
    font-size: 1.2rem;
    color: #888;
    margin: 2rem 0;
}
.pagination {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-top: 2rem;
}
.status-btn {
    border: none;
    border-radius: 8px;
    padding: .5rem 1.5rem;
    font-size: 1rem;
    cursor: pointer;
}
.status-btn.active {
    background: #4CAF50;
    color: #fff;
}

.status-btn.inactive {
    background: #888;
    color: #fff;
}
</style>
