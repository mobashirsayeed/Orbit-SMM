<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
      </svg>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
        Two-Factor Authentication
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
        Enter the code from your authenticator app
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form @submit.prevent="submit" class="space-y-6">
          <div>
            <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Authentication Code
            </label>
            <div class="mt-1">
              <input
                id="code"
                v-model="form.code"
                type="text"
                maxlength="6"
                pattern="[0-9]*"
                inputmode="numeric"
                required
                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orbit-500 focus:border-orbit-500 dark:bg-gray-700 dark:text-white text-center text-2xl tracking-widest"
                placeholder="000000"
              />
            </div>
            <p v-if="errors.code" class="mt-2 text-sm text-red-600">{{ errors.code }}</p>
          </div>

          <div>
            <button
              type="submit"
              :disabled="processing"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orbit-600 hover:bg-orbit-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orbit-500 disabled:opacity-50"
            >
              {{ processing ? 'Verifying...' : 'Verify' }}
            </button>
          </div>

          <div class="text-center">
            <button
              type="button"
              @click="showRecovery = !showRecovery"
              class="text-sm font-medium text-orbit-600 hover:text-orbit-500"
            >
              Use a recovery code instead
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const processing = ref(false);
const showRecovery = ref(false);
const errors = ref({});

const form = useForm({
  code: '',
});

const submit = () => {
  processing.value = true;
  form.post('/two-factor-challenge', {
    onSuccess: () => {
      processing.value = false;
    },
    onError: (e) => {
      errors.value = e;
      processing.value = false;
    },
  });
};
</script>
