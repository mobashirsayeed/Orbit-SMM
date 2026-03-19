<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inbox</h1>
        <div class="flex items-center space-x-3">
          <button
            @click="syncInbox"
            :disabled="syncing"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <svg v-if="syncing" class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ syncing ? 'Syncing...' : 'Sync' }}
          </button>
        </div>
      </div>
    </template>

    <div class="flex h-[calc(100vh-12rem)]">
      <!-- Conversation List -->
      <div class="w-96 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
        <!-- Filters -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex space-x-2 mb-3">
            <button
              @click="filter = 'all'"
              :class="[
                'px-3 py-1 rounded-md text-sm font-medium',
                filter === 'all' ? 'bg-orbit-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              ]"
            >
              All
            </button>
            <button
              @click="filter = 'unread'"
              :class="[
                'px-3 py-1 rounded-md text-sm font-medium',
                filter === 'unread' ? 'bg-orbit-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              ]"
            >
              Unread
            </button>
            <button
              @click="filter = 'assigned'"
              :class="[
                'px-3 py-1 rounded-md text-sm font-medium',
                filter === 'assigned' ? 'bg-orbit-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
              ]"
            >
              Assigned to Me
            </button>
          </div>
          <input
            v-model="search"
            type="text"
            placeholder="Search conversations..."
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm dark:bg-gray-700 dark:text-white"
          />
        </div>

        <!-- Stats -->
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700">
          <div class="grid grid-cols-2 gap-2 text-center">
            <div>
              <div class="text-2xl font-bold text-orbit-600">{{ stats.unread }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Unread</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ stats.open }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Open</div>
            </div>
          </div>
        </div>

        <!-- Conversations -->
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="conversation in conversations.data"
            :key="conversation.id"
            @click="selectConversation(conversation)"
            :class="[
              'p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700',
              selectedConversation?.id === conversation.id ? 'bg-orbit-50 dark:bg-gray-700' : ''
            ]"
          >
            <div class="flex justify-between items-start">
              <div class="flex items-center space-x-3">
                <img
                  :src="conversation.contact.avatar_url || '/default-avatar.png'"
                  class="w-10 h-10 rounded-full"
                />
                <div>
                  <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ conversation.contact.name }}
                  </h3>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ getPlatformName(conversation.platform) }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span
                  v-if="conversation.unread_count > 0"
                  class="px-2 py-0.5 bg-orbit-600 text-white text-xs rounded-full"
                >
                  {{ conversation.unread_count }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(conversation.last_message_at) }}
                </span>
              </div>
            </div>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 truncate">
              {{ conversation.messages[0]?.body || 'No messages yet' }}
            </p>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="conversations.last_page > 1" class="p-4 border-t border-gray-200 dark:border-gray-700">
          <div class="flex justify-between">
            <button
              @click="loadPage(conversations.current_page - 1)"
              :disabled="conversations.current_page === 1"
              class="text-sm text-orbit-600 hover:text-orbit-500 disabled:opacity-50"
            >
              Previous
            </button>
            <span class="text-sm text-gray-600 dark:text-gray-400">
              Page {{ conversations.current_page }} of {{ conversations.last_page }}
            </span>
            <button
              @click="loadPage(conversations.current_page + 1)"
              :disabled="conversations.current_page === conversations.last_page"
              class="text-sm text-orbit-600 hover:text-orbit-500 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Conversation Detail -->
      <div class="flex-1 bg-white dark:bg-gray-800 overflow-y-auto">
        <div v-if="selectedConversation" class="h-full flex flex-col">
          <!-- Header -->
          <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-3">
                <img
                  :src="selectedConversation.contact.avatar_url || '/default-avatar.png'"
                  class="w-10 h-10 rounded-full"
                />
                <div>
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedConversation.contact.name }}
                  </h2>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ getPlatformName(selectedConversation.platform) }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="assignConversation"
                  class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                  title="Assign"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </button>
                <button
                  @click="toggleStar"
                  class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                  title="Star"
                >
                  <svg
                    :class="['w-5 h-5', selectedConversation.is_starred ? 'text-yellow-500 fill-current' : 'text-gray-400']"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                  </svg>
                </button>
                <button
                  @click="closeConversation"
                  class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                  title="Close"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div
              v-for="message in selectedConversation.messages"
              :key="message.id"
              :class="[
                'flex',
                message.direction === 'inbound' ? 'justify-start' : 'justify-end'
              ]"
            >
              <div
                :class="[
                  'max-w-md px-4 py-2 rounded-lg',
                  message.direction === 'inbound'
                    ? 'bg-gray-100 dark:bg-gray-700'
                    : 'bg-orbit-600 text-white'
                ]"
              >
                <p class="text-sm">{{ message.body }}</p>
                <p class="text-xs mt-1 opacity-75">
                  {{ formatMessageTime(message.created_at) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Reply Box -->
          <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <!-- Quick Replies -->
            <div class="mb-3 flex space-x-2 overflow-x-auto">
              <button
                v-for="template in quickReplies"
                :key="template.id"
                @click="insertTemplate(template)"
                class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 whitespace-nowrap"
              >
                {{ template.name }}
              </button>
            </div>
            <form @submit.prevent="sendMessage" class="flex space-x-3">
              <input
                v-model="replyBody"
                type="text"
                placeholder="Type a reply..."
                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
              />
              <button
                type="submit"
                :disabled="!replyBody.trim()"
                class="px-4 py-2 bg-orbit-600 text-white rounded-md hover:bg-orbit-700 disabled:opacity-50"
              >
                Send
              </button>
            </form>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="h-full flex items-center justify-center">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Select a conversation</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Choose from the list to view messages
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
  conversations: Object,
  stats: Object,
  pollingInterval: Number,
});

const filter = ref('all');
const search = ref('');
const selectedConversation = ref(null);
const replyBody = ref('');
const syncing = ref(false);
const quickReplies = ref([]);

let pollingInterval = null;

const getPlatformName = (platform) => {
  const names = {
    facebook: 'Facebook',
    twitter: 'Twitter',
    linkedin: 'LinkedIn',
    instagram: 'Instagram',
  };
  return names[platform] || platform;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatMessageTime = (date) => {
  return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const selectConversation = (conversation) => {
  selectedConversation.value = conversation;
  router.visit(`/inbox/${conversation.id}`, {
    preserveState: true,
    onSuccess: (page) => {
      selectedConversation.value = page.props.conversation;
    },
  });
};

const sendMessage = () => {
  if (!replyBody.value.trim()) return;

  router.post(`/inbox/${selectedConversation.value.id}/messages`, {
    body: replyBody.value,
  }, {
    onSuccess: () => {
      replyBody.value = '';
    },
  });
};

const insertTemplate = (template) => {
  replyBody.value = template.content;
};

const toggleStar = () => {
  router.post(`/inbox/conversations/${selectedConversation.value.id}/star`);
};

const closeConversation = () => {
  router.post(`/inbox/conversations/${selectedConversation.value.id}/close`);
};

const assignConversation = () => {
  router.post(`/inbox/conversations/${selectedConversation.value.id}/assign`, {
    user_id: 'me',
  });
};

const syncInbox = async () => {
  syncing.value = true;
  router.post('/inbox/sync', {}, {
    onFinish: () => {
      syncing.value = false;
    },
  });
};

const loadPage = (page) => {
  router.get(`/inbox?page=${page}`, {}, {
    preserveState: true,
  });
};

// Polling for new messages
const startPolling = () => {
  pollingInterval = setInterval(async () => {
    try {
      const response = await fetch('/inbox/api/poll');
      const data = await response.json();
      if (data.new_messages.length > 0) {
        // Refresh conversation
        router.reload({ only: ['conversations'] });
      }
    } catch (error) {
      console.error('Polling failed:', error);
    }
  }, props.pollingInterval || 10000);
};

onMounted(() => {
  startPolling();
});

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
});
</script>
