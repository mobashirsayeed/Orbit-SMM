<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import TopBar from '@/Components/TopBar.vue';
import { ref } from 'vue';

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />
        <Sidebar :open="sidebarOpen" @close="sidebarOpen = false" />
        <div class="lg:pl-60">
            <TopBar @toggle-sidebar="sidebarOpen = !sidebarOpen" />
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
