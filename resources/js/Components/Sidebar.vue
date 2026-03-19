<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    open: { type: Boolean, default: false },
});

defineEmits(['close']);

const page = usePage();
const currentPath = computed(() => page.url);

const modules = [
    { name: 'Dashboard', href: '/dashboard' },
    { name: 'Inbox', href: '/inbox' },
    { name: 'Social', href: '/social/composer' },
    { name: 'SEO', href: '/seo/audit' },
    { name: 'Google Business', href: '/gbp' },
    { name: 'Ads', href: '/ads' },
    { name: 'AI Studio', href: '/ai/content' },
    { name: 'Analytics', href: '/analytics' },
    { name: 'CRM', href: '/crm/contacts' },
    { name: 'Agency', href: '/agency/clients' },
];

const isActive = (href) => currentPath.value.startsWith(href);
</script>

<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 w-60 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-transform lg:translate-x-0',
            open ? 'translate-x-0' : '-translate-x-full'
        ]"
    >
        <div class="flex items-center h-16 px-6 border-b border-gray-200 dark:border-gray-700">
            <Link href="/dashboard" class="text-2xl font-bold text-orbit-600">ORBIT</Link>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <Link
                v-for="item in modules"
                :key="item.name"
                :href="item.href"
                :class="[
                    'flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                    isActive(item.href)
                        ? 'bg-orbit-50 dark:bg-orbit-900/30 text-orbit-700'
                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50'
                ]"
                @click="$emit('close')"
            >
                {{ item.name }}
            </Link>
        </nav>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-xs text-gray-500 uppercase">Plan</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">
                    {{ $page.props.workspace?.plan || 'Starter' }}
                </p>
            </div>
        </div>
    </aside>
</template>
