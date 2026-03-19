<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Top Navigation -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Left Side -->
          <div class="flex">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
              <Link href="/dashboard" class="flex items-center">
                <div class="w-8 h-8 bg-orbit-600 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16z"/>
                    <path d="M10 4a6 6 0 100 12 6 6 0 000-12z"/>
                  </svg>
                </div>
                <span class="ml-2 text-xl font-bold text-gray-900 dark:text-white">Orbit</span>
              </Link>
            </div>

            <!-- Navigation Links -->
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <Link
                v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                :class="[
                  'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium',
                  route().current(item.current)
                    ? 'border-orbit-500 text-gray-900 dark:text-white'
                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400'
                ]"
              >
                {{ item.name }}
              </Link>
            </div>
          </div>

          <!-- Right Side -->
          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button
              @click="toggleNotifications"
              class="p-2 rounded-full text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
            >
              <span class="sr-only">View notifications</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
              <span v-if="unreadCount > 0" class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- Workspace Switcher -->
            <div class="relative">
              <button
                @click="toggleWorkspaceMenu"
                class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900"
              >
                <span>{{ currentWorkspace?.name }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>
            </div>

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="toggleUserMenu"
                class="flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900"
              >
                <img
                  v-if="$page.props.auth.user.avatar_url"
                  :src="$page.props.auth.user.avatar_url"
                  class="w-8 h-8 rounded-full"
                />
                <div v-else class="w-8 h-8 rounded-full bg-orbit-600 flex items-center justify-center text-white font-medium">
                  {{ $page.props.auth.user.name.charAt(0) }}
                </div>
                <span class="hidden md:block">{{ $page.props.auth.user.name }}</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <!-- Dropdown Menu -->
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50"
              >
                <Link
                  href="/settings/profile"
                  class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  Profile
                </Link>
                <Link
                  href="/settings/workspace"
                  class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  Workspace Settings
                </Link>
                <Link
                  href="/logout"
                  method="post"
                  as="button"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  Sign out
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Sidebar -->
    <div class="flex">
      <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 min-h-screen">
        <nav class="mt-5 px-4">
          <div v-for="section in sidebarSections" :key="section.name" class="mb-8">
            <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
              {{ section.name }}
            </h3>
            <div class="mt-2 space-y-1">
              <Link
                v-for="item in section.items"
                :key="item.name"
                :href="item.href"
                :class="[
                  'group flex items-center px-3 py-2 text-sm font-medium rounded-md',
                  route().current(item.current)
                    ? 'bg-orbit-50 dark:bg-gray-700 text-orbit-600'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                ]"
              >
                <component
                  :is="item.icon"
                  :class="[
                    'mr-3 flex-shrink-0 h-5 w-5',
                    route().current(item.current)
                      ? 'text-orbit-600'
                      : 'text-gray-400 group-hover:text-gray-500'
                  ]"
                />
                {{ item.name }}
              </Link>
            </div>
          </div>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1">
        <!-- Page Header -->
        <div v-if="$slots.header" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
          <div class="px-4 py-4 sm:px-6 lg:px-8">
            <slot name="header" />
          </div>
        </div>

        <!-- Page Content -->
        <div class="p-4 sm:p-6 lg:p-8">
          <slot />
        </div>
      </main>
    </div>

    <!-- Toast Notifications -->
    <ToastNotifications />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
  HomeIcon,
  CalendarIcon,
  ChatBubbleLeftRightIcon,
  ChartBarIcon,
  SparklesIcon,
  MagnifyingGlassIcon,
  BuildingOfficeIcon,
  UserGroupIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline';
import ToastNotifications from '@/Components/UI/ToastNotifications.vue';

const page = usePage();
const userMenuOpen = ref(false);
const workspaceMenuOpen = ref(false);
const notificationsOpen = ref(false);
const unreadCount = ref(0);

const navigation = [
  { name: 'Dashboard', href: '/dashboard', current: 'dashboard' },
  { name: 'Analytics', href: '/analytics', current: 'analytics.*' },
];

const sidebarSections = [
  {
    name: 'Main',
    items: [
      { name: 'Dashboard', href: '/dashboard', current: 'dashboard', icon: HomeIcon },
      { name: 'Content Calendar', href: '/social/calendar', current: 'social.calendar', icon: CalendarIcon },
      { name: 'Create Post', href: '/social/composer', current: 'social.composer', icon: SparklesIcon },
    ]
  },
  {
    name: 'Engagement',
    items: [
      { name: 'Inbox', href: '/inbox', current: 'inbox.*', icon: ChatBubbleLeftRightIcon },
      { name: 'Analytics', href: '/analytics', current: 'analytics.*', icon: ChartBarIcon },
    ]
  },
  {
    name: 'Tools',
    items: [
      { name: 'AI Studio', href: '/ai/studio', current: 'ai.*', icon: SparklesIcon },
      { name: 'SEO Toolkit', href: '/seo', current: 'seo.*', icon: MagnifyingGlassIcon },
      { name: 'Google Business', href: '/google-business', current: 'gbp.*', icon: BuildingOfficeIcon },
      { name: 'CRM', href: '/crm', current: 'crm.*', icon: UserGroupIcon },
    ]
  },
  {
    name: 'Settings',
    items: [
      { name: 'Settings', href: '/settings', current: 'settings.*', icon: Cog6ToothIcon },
    ]
  }
];

const currentWorkspace = computed(() => page.props.workspace);

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value;
  workspaceMenuOpen.value = false;
  notificationsOpen.value = false;
};

const toggleWorkspaceMenu = () => {
  workspaceMenuOpen.value = !workspaceMenuOpen.value;
  userMenuOpen.value = false;
  notificationsOpen.value = false;
};

const toggleNotifications = () => {
  notificationsOpen.value = !notificationsOpen.value;
  userMenuOpen.value = false;
  workspaceMenuOpen.value = false;
};

// Close dropdowns on click outside
const closeDropdowns = () => {
  userMenuOpen.value = false;
  workspaceMenuOpen.value = false;
  notificationsOpen.value = false;
};
</script>
