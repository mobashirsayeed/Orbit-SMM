<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">SEO Toolkit</h1>
        <button
          @click="showAddMonitor = true"
          class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
        >
          Add Website
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-100">
              <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg SEO Score</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">78</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-100">
              <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Keywords Tracked</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ monitors.length * 5 }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-yellow-100">
              <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Issues Found</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">23</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-purple-100">
              <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Position</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">12.4</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Monitored Websites -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Monitored Websites</h2>
        </div>

        <DataTable
          :data="monitors"
          :columns="columns"
          @page-change="loadPage"
        >
          <template #cell-score="{ value }">
            <span :class="[
              'px-2 py-1 rounded-full text-xs font-medium',
              value >= 80 ? 'bg-green-100 text-green-800' :
              value >= 60 ? 'bg-yellow-100 text-yellow-800' :
              'bg-red-100 text-red-800'
            ]">
              {{ value }}
            </span>
          </template>

          <template #cell-last_check_at="{ value }">
            {{ value ? new Date(value).toLocaleDateString() : 'Never' }}
          </template>

          <template #actions="{ item }">
            <div class="flex justify-end space-x-2">
              <button
                @click="runAudit(item)"
                class="text-orbit-600 hover:text-orbit-500"
              >
                Audit
              </button>
              <button
                @click="viewDetails(item)"
                class="text-blue-600 hover:text-blue-500"
              >
                View
              </button>
              <button
                @click="deleteMonitor(item)"
                class="text-red-600 hover:text-red-500"
              >
                Delete
              </button>
            </div>
          </template>
        </DataTable>
      </div>

      <!-- Add Monitor Modal -->
      <Modal
        :show="showAddMonitor"
        title="Add Website to Monitor"
        @close="showAddMonitor = false"
      >
        <form @submit.prevent="addMonitor">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Website URL
            </label>
            <input
              v-model="form.domain"
              type="url"
              required
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
              placeholder="https://example.com"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Keywords to Track (comma-separated)
            </label>
            <textarea
              v-model="form.keywords"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
              placeholder="seo tools, social media management, etc."
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showAddMonitor = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700 disabled:opacity-50"
            >
              {{ processing ? 'Adding...' : 'Add Website' }}
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
import DataTable from '@/Components/UI/DataTable.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
  monitors: Array,
});

const showAddMonitor = ref(false);
const processing = ref(false);

const form = ref({
  domain: '',
  keywords: '',
});

const columns = [
  { key: 'domain', label: 'Website', sortable: true },
  { key: 'seo_score', label: 'SEO Score', sortable: true, format: 'number' },
  { key: 'keywords', label: 'Keywords', sortable: false },
  { key: 'last_check_at', label: 'Last Check', sortable: true, format: 'date' },
];

const addMonitor = () => {
  processing.value = true;
  router.post('/seo/monitors', {
    domain: form.value.domain,
    keywords: form.value.keywords.split(',').map(k => k.trim()).filter(k => k),
  }, {
    onSuccess: () => {
      processing.value = false;
      showAddMonitor.value = false;
      form.value = { domain: '', keywords: '' };
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const runAudit = (monitor) => {
  router.post(`/seo/monitors/${monitor.id}/crawl`);
};

const viewDetails = (monitor) => {
  router.get(`/seo/monitors/${monitor.id}/audit`);
};

const deleteMonitor = (monitor) => {
  if (confirm('Are you sure you want to delete this monitor?')) {
    router.delete(`/seo/monitors/${monitor.id}`);
  }
};

const loadPage = (page) => {
  router.get(`/seo?page=${page}`, {}, { preserveState: true });
};
</script>
