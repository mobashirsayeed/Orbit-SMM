<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const workspace = computed(() => usePage().props.workspace);
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back 👋</h1>
                <p class="text-gray-500 mt-1">
                    Here's what's happening in {{ workspace?.name }}
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    v-for="s in [
                        {n:'Inbox Messages',v:'0'},
                        {n:'Scheduled Posts',v:'0'},
                        {n:'AI Credits',v:'0'},
                        {n:'Total Reach',v:'0'},
                        {n:'Contacts',v:'0'},
                        {n:'SEO Score',v:'—'}
                    ]"
                    :key="s.n"
                    class="card p-6"
                >
                    <p class="text-sm text-gray-500">{{ s.n }}</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ s.v }}</p>
                </div>
            </div>
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a
                        v-for="a in [
                            {href:'/social/composer',label:'New Post'},
                            {href:'/ai/content',label:'AI Write'},
                            {href:'/inbox',label:'Check Inbox'},
                            {href:'/analytics',label:'Analytics'}
                        ]"
                        :key="a.href"
                        :href="a.href"
                        class="flex flex-col items-center p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                    >
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ a.label }}
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
