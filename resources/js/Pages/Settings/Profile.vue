<template>
  <AppLayout>
    <template #header>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile Settings</h1>
    </template>

    <div class="max-w-3xl space-y-6">
      <!-- Profile Information -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Profile Information</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Update your account's profile information</p>
        </div>

        <form @submit.prevent="updateProfile" class="px-6 py-4 space-y-4">
          <!-- Avatar -->
          <div class="flex items-center space-x-4">
            <img
              :src="avatarPreview || $page.props.auth.user.avatar_url || '/default-avatar.png'"
              class="w-20 h-20 rounded-full object-cover"
            />
            <div>
              <button
                type="button"
                @click="$refs.avatarInput.click()"
                class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
              >
                Change Avatar
              </button>
              <input
                ref="avatarInput"
                type="file"
                @change="handleAvatarChange"
                accept="image/*"
                class="hidden"
              />
              <p class="text-xs text-gray-500 mt-1">JPG, PNG. Max 2MB</p>
            </div>
          </div>

          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="processing"
              class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700 disabled:opacity-50"
            >
              {{ processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Change Password -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Change Password</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Ensure your account is secure</p>
        </div>

        <form @submit.prevent="updatePassword" class="px-6 py-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Password</label>
            <input
              v-model="passwordForm.current_password"
              type="password"
              class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            />
            <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">
              {{ passwordErrors.current_password }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
            <input
              v-model="passwordForm.password"
              type="password"
              class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            />
            <p v-if="passwordErrors.password" class="mt-1 text-sm text-red-600">
              {{ passwordErrors.password }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
            <input
              v-model="passwordForm.password_confirmation"
              type="password"
              class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="passwordProcessing"
              class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700 disabled:opacity-50"
            >
              {{ passwordProcessing ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Two-Factor Authentication -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Two-Factor Authentication</h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security</p>
        </div>

        <div class="px-6 py-4">
          <div v-if="!twoFactorEnabled" class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">2FA is disabled</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Protect your account with two-factor authentication</p>
            </div>
            <button
              @click="enable2FA"
              class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
            >
              Enable 2FA
            </button>
          </div>

          <div v-else class="flex justify-between items-center">
            <div>
              <p class="text-sm font-medium text-green-600">2FA is enabled</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">Your account is protected</p>
            </div>
            <button
              @click="disable2FA"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              Disable 2FA
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const page = usePage();

const processing = ref(false);
const passwordProcessing = ref(false);
const avatarPreview = ref(null);
const twoFactorEnabled = ref(false);

const form = useForm({
  name: page.props.auth.user.name,
  email: page.props.auth.user.email,
  avatar: null,
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const errors = ref({});
const passwordErrors = ref({});

const handleAvatarChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.avatar = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      avatarPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const updateProfile = () => {
  processing.value = true;
  form.post('/settings/profile', {
    onSuccess: () => {
      processing.value = false;
    },
    onError: (e) => {
      errors.value = e;
      processing.value = false;
    },
  });
};

const updatePassword = () => {
  passwordProcessing.value = true;
  passwordForm.put('/settings/password', {
    onSuccess: () => {
      passwordProcessing.value = false;
      passwordForm.reset();
    },
    onError: (e) => {
      passwordErrors.value = e;
      passwordProcessing.value = false;
    },
  });
};

const enable2FA = () => {
  router.post('/two-factor/enable');
};

const disable2FA = () => {
  router.post('/two-factor/disable');
};
</script>
