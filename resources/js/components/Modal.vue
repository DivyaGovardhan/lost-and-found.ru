<template>
    <div v-if="visible" class="modal" @click.self="close">
        <div class="modal-content">
            <slot />
        </div>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    visible: Boolean,
});

const emit = defineEmits(['update:visible']);

function close() {
    emit('update:visible', false);
}
</script>

<style scoped>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0; top: 0; right: 0; bottom: 0;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    padding: 1.5rem 3rem;
    border-radius: 8px;
    position: relative;
    max-width: 600px;
    width: 100%;
}
</style>
