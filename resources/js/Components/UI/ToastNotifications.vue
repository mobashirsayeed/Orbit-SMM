<template>
  <div class="fixed bottom-4 right-4 z-50 space-y-2">
    <transition-group
      enter-active-class="transform transition ease-out duration-300"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'px-4 py-3 rounded-lg shadow-lg flex items-center space-x-3 min-w-72',
          toast.type === 'success' ? 'bg-green-500' : '',
          toast.type === 'error' ? 'bg-red-500' : '',
          toast.type === 'warning' ? 'bg-yellow-500' : '',
          toast.type === 'info' ? 'bg-blue-500' : '',
        ]"
      >
        <svg v-if="toast.type === 'success'" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <svg v-else-if="toast.type === 'error'" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <svg v-else-if="toast.type === 'warning'" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <svg v-else class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-white text-sm flex-1">{{ toast.message }}</span>
        <button @click="remove(toast.id)" class="text-white hover:text-gray-200">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toasts = ref([]);

onMounted(() => {
  if (page.props.flash?.success) {
    addToast(page.props.flash.success, 'success');
  }
  if (page.props.flash?.error) {
    addToast(page.props.flash.error, 'error');
  }
  if (page.props.flash?.warning) {
    addToast(page.props.flash.warning, 'warning');
  }
  if (page.props.flash?.info) {
    addToast(page.props.flash.info, 'info');
  }
});

const addToast = (message, type = 'info') => {
  const id = Date.now();
  toasts.value.push({ id, message, type });
  setTimeout(() => remove(id), 5000);
};

const remove = (id) => {
  toasts.value = toasts.value.filter(t => t.id !== id);
};

defineExpose({ addToast });
</script>
