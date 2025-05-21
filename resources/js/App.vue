<template>
    <div class="app-container">
        <header>
            <div class="logo-container" @click="goHome" style="cursor: pointer;">
                <img src="../img/logo.svg" alt="logo" style="width: 60px; height: 70px;" />
                <div class="header1">
                    <h1>БЮРО НАХОДОК</h1>
                    <p>Потерянные вещи г.Томск</p>
                </div>
            </div>

            <nav>
                <a v-if="!auth.state.user" href="#" @click.prevent="openRegister">Регистрация</a>
                <a v-if="!auth.state.user" href="#" @click.prevent="openLogin">Вход</a>

                <button v-if="auth.state.user" @click="openCreatePost" class="nav-btn">Создать объявление</button>
                <a v-if="auth.state.user" href="#" @click.prevent="openMyPosts">Мои объявления</a>
                <a href="#" @click.prevent="goHome">Главная</a>
                <a href="#" v-if="auth.state.user" @click.prevent="auth.logout">Выйти</a>
            </nav>
        </header>

        <main>
            <PostsList v-if="!selectedPostId && !showMyPosts" @open-post="openPost" />
            <PostDetails v-else-if="selectedPostId" :postId="selectedPostId" @back="selectedPostId = null" />
            <MyPosts v-else-if="showMyPosts" />
        </main>


        <RegisterModal
            :visible="showRegister"
            @update:visible="val => showRegister = val"
            @switch-to-login="switchToLogin"
        />
        <LoginModal
            :visible="showLogin"
            @update:visible="val => showLogin = val"
            @switch-to-register="switchToRegister"
        />
        <CreatePostModal
            :visible="showCreatePost"
            @update:visible="val => showCreatePost = val"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import useAuth from './composables/useAuth';
import RegisterModal from './components/RegisterModal.vue';
import LoginModal from './components/LoginModal.vue';
import CreatePostModal from './components/CreatePostModal.vue';
import PostsList from './components/PostsList.vue';
import PostDetails from './components/PostDetails.vue';
import MyPosts from './components/MyPosts.vue';

const auth = useAuth();

const showRegister = ref(false);
const showLogin = ref(false);
const showCreatePost = ref(false);
const selectedPostId = ref(null);
const showMyPosts = ref(false);

function goHome() {
    // Сбросить все состояния, чтобы показать главную страницу со списком объявлений
    selectedPostId.value = null;
    showMyPosts.value = false;
    showRegister.value = false;
    showLogin.value = false;
    showCreatePost.value = false;
}

function openPost(id) {
    selectedPostId.value = id;
}

function openMyPosts() {
    showMyPosts.value = true;
    // Скрываем другие страницы/модалки
    selectedPostId.value = null;
    showRegister.value = false;
    showLogin.value = false;
    showCreatePost.value = false;
}

function openRegister() {
    showRegister.value = true;
    showLogin.value = false;
}

function openLogin() {
    showLogin.value = true;
    showRegister.value = false;
}

function openCreatePost() {
    showCreatePost.value = true;
}

function switchToLogin() {
    showRegister.value = false;
    showLogin.value = true;
}

function switchToRegister() {
    showLogin.value = false;
    showRegister.value = true;
}
</script>

<style>
/* Добавьте ваши стили, например стили для header, nav, модалок и т.д. */
</style>
