<template>
  <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between h-16 px-6">
      <!-- Left: Page Title Slot -->
      <div class="flex items-center">
        <slot name="title">
          <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
        </slot>
      </div>

      <!-- Right: User Menu -->
      <div class="flex items-center space-x-4">
        <!-- Notifications -->
        <button class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white rounded-md">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </button>

        <!-- User Avatar -->
        <div class="relative">
          <button class="flex items-center space-x-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
            <img
              v-if="$page.props.auth?.user?.avatar_url"
              :src="$page.props.auth.user.avatar_url"
              class="w-8 h-8 rounded-full object-cover"
              :alt="$page.props.auth?.user?.name"
            />
            <div v-else class="w-8 h-8 rounded-full bg-orbit-600 flex items-center justify-center text-white text-xs font-bold">
              {{ userInitials }}
            </div>
            <span class="hidden md:block">{{ $page.props.auth?.user?.name }}</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    default: '',
  },
});

const page = usePage();
const userInitials = computed(() => {
  const name = page.props.auth?.user?.name || '';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});
</script>
