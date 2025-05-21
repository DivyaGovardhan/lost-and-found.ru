<template>
    <div class="container post-details-container">
        <h2 class="post-title">{{ post?.title }}</h2>
        <div class="post-details-content">
            <!-- Фото и статус -->
            <div class="post-image-block">
                <div class="image-wrapper">
                    <img
                        :src="post?.photo ? '/storage/' + post.photo : '../../img/default.png'"
                        alt="Фото предмета"
                        class="post-image"
                    />
                </div>
            </div>
            <!-- Правая часть: инфо -->
            <div class="post-info-block">
                <div class="post-meta">
                    <span class="post-date">{{ formatDate(post?.date) }}</span>
                    <span class="post-year">{{ formatYear(post?.date) }}</span>
                    <span class="post-address">
                        <b>
                            Адрес: {{ post?.district?.name }} район
                            {{ post?.street ? `, улица ${post.street}` : '' }}
                            {{ post?.house ? `, дом ${post.house}` : '' }}
                        </b>
                    </span>
                </div>
                <div class="post-description">
                    {{ post?.description }}
                </div>
                <div class="post-contacts">
                    <div class="contact-row">
                        <img src="../../img/user-icon.svg" class="contact-icon" alt="Пользователь" />
                        <span>{{ post?.user?.name }}</span>
                    </div>
                    <div
                        v-for="(contact, idx) in post?.user?.contacts || []"
                        :key="idx"
                        class="contact-row"
                    >
                        <template v-if="isPhone(contact.contact)">
                            <img src="../../img/phone-icon.svg" class="contact-icon" alt="Телефон" />
                            <span>{{ contact.contact }}</span>
                        </template>
                        <template v-else>
                            <img src="../../img/contact-icon.svg" class="contact-icon" alt="Ссылка" />
                            <a :href="contact.contact" target="_blank">{{ contact.contact }}</a>
                        </template>
                    </div>
                </div>
                <div class="post-actions">
                    <button class="complain-btn" @click="complain">Пожаловаться на объявление</button>
                </div>
                <button class="back-btn"  @click="$emit('back')">Вернуться назад</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';

const props = defineProps({
    postId: { type: [String, Number], required: true }
});

const post = ref(null);

onMounted(() => {
    fetchPost();
});

async function fetchPost() {
    try {
        const { data } = await api.get(`/posts/${props.postId}`);
        post.value = data;
    } catch (e) {
        alert('Ошибка загрузки объявления');
    }
}

async function complain() {
    try {
        // Отправляем жалобу на сервер
        await api.post(`/posts/${post.value.id}/complaints`);

        // Обновляем локальное состояние (если нужно)
        if (post.value.complaint_number !== undefined) {
            post.value.complaint_number += 1;
        }

        alert('Жалоба успешно отправлена');
    } catch (e) {
        console.error('Ошибка при отправке жалобы:', e);
        alert('Не удалось отправить жалобу');
    }
}

// Остальные функции остаются без изменений
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('ru-RU');
}
function formatYear(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.getFullYear();
}

function isPhone(str) {
    return /^(\+7|8)\d{10,}$/.test(str.replace(/[\s\-()]/g, ''));
}

function goBack() {
    window.history.back();
}
</script>

<style scoped>
.post-details-container {
    margin-top: 2rem;
}
.post-title {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 1.5rem;
}
.post-details-content {
    display: flex;
    gap: 2rem;
}
.post-image-block {
    flex: 0 0 400px;
}
.image-wrapper {
    position: relative;
    width: 400px;
    height: 400px;
    background: #eee;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.post-image {
    max-width: 100%;
    max-height: 100%;
    border-radius: 15px;
}
.status-flag {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 6px 18px;
    border-radius: 8px;
    color: #fff;
    font-weight: bold;
    background: #ff9800;
    font-size: 1rem;
}
.status-flag.found {
    background: #ff9800;
}
.status-flag.lost {
    background: #F44336;
}
.post-info-block {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.post-meta {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    font-size: 1.1rem;
}
.post-date {
    color: #2196f3;
    font-weight: bold;
}
.post-year {
    color: #4caf50;
    font-weight: bold;
}
.post-address {
    font-weight: bold;
}
.post-description {
    margin: 1rem 0;
    color: #333;
    font-size: 1.1rem;
}
.post-views {
    color: #666;
    margin-bottom: 1rem;
}
.post-contacts {
    margin-bottom: 1rem;
}
.contact-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.5rem;
}
.contact-icon {
    width: 24px;
    height: 24px;
}
.post-actions {
    margin-bottom: 1.5rem;
}
.complain-btn {
    background: none;
    color: #ff9800;
    border: none;
    font-size: 1rem;
    text-decoration: underline;
    cursor: pointer;
    padding: 0;
}
.back-btn {
    background: #FC443A;
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 0.75rem 2rem;
    font-size: 1rem;
    cursor: pointer;
    margin-top: 2rem;
}
</style>
