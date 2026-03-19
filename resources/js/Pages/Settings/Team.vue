<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Members</h1>
        <button
          @click="showInvite = true"
          class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
        >
          Invite Member
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Current Members -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Current Members</h2>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="member in members"
            :key="member.id"
            class="px-6 py-4 flex items-center justify-between"
          >
            <div class="flex items-center space-x-4">
              <img
                :src="member.avatar_url || '/default-avatar.png'"
                class="w-10 h-10 rounded-full"
              />
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ member.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ member.email }}</p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium capitalize',
                  member.pivot.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                  member.pivot.role === 'editor' ? 'bg-blue-100 text-blue-800' :
                  'bg-gray-100 text-gray-800'
                ]"
              >
                {{ member.pivot.role }}
              </span>
              <button
                v-if="member.id !== $page.props.auth.user.id"
                @click="removeMember(member)"
                class="text-red-600 hover:text-red-500 text-sm"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Invitations -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Pending Invitations</h2>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="invitation in invitations"
            :key="invitation.id"
            class="px-6 py-4 flex items-center justify-between"
          >
            <div>
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ invitation.email }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Invited {{ formatDate(invitation.created_at) }} • Expires {{ formatDate(invitation.expires_at) }}
              </p>
            </div>
            <div class="flex items-center space-x-4">
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium capitalize',
                  invitation.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                  invitation.role === 'editor' ? 'bg-blue-100 text-blue-800' :
                  'bg-gray-100 text-gray-800'
                ]"
              >
                {{ invitation.role }}
              </span>
              <button
                @click="revokeInvitation(invitation)"
                class="text-red-600 hover:text-red-500 text-sm"
              >
                Revoke
              </button>
            </div>
          </div>

          <div v-if="invitations.length === 0" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
            No pending invitations
          </div>
        </div>
      </div>

      <!-- Invite Modal -->
      <Modal
        :show="showInvite"
        title="Invite Team Member"
        @close="showInvite = false"
      >
        <form @submit.prevent="sendInvite">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Email Address
            </label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Role
            </label>
            <select
              v-model="form.role"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            >
              <option value="viewer">Viewer (Read-only)</option>
              <option value="editor">Editor (Can create & edit)</option>
              <option value="admin">Admin (Full access)</option>
            </select>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showInvite = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700 disabled:opacity-50"
            >
              {{ processing ? 'Sending...' : 'Send Invite' }}
            </button>
          </div>
        </form>
      </Modal>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
  members: Array,
  invitations: Array,
});

const showInvite = ref(false);
const processing = ref(false);

const form = ref({
  email: '',
  role: 'viewer',
});

const sendInvite = () => {
  processing.value = true;
  router.post('/team/invitations', form.value, {
    onSuccess: () => {
      processing.value = false;
      showInvite.value = false;
      form.value = { email: '', role: 'viewer' };
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const removeMember = (member) => {
  if (confirm('Are you sure you want to remove this member?')) {
    router.delete(`/team/members/${member.id}`);
  }
};

const revokeInvitation = (invitation) => {
  if (confirm('Are you sure you want to revoke this invitation?')) {
    router.delete(`/team/invitations/${invitation.id}`);
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};
</script>
