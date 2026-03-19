<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sales Pipeline</h1>
        <button
          @click="showCreateDeal = true"
          class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
        >
          + New Deal
        </button>
      </div>
    </template>

    <div class="h-[calc(100vh-12rem)] overflow-x-auto">
      <div class="flex space-x-4 min-w-max">
        <!-- Pipeline Stages -->
        <div
          v-for="stage in pipeline.stages"
          :key="stage.id"
          class="w-80 flex-shrink-0"
        >
          <!-- Stage Header -->
          <div
            class="px-4 py-3 rounded-t-lg"
            :style="{ backgroundColor: stage.color || '#e5e7eb' }"
          >
            <div class="flex justify-between items-center">
              <h3 class="font-medium text-white">{{ stage.name }}</h3>
              <span class="px-2 py-1 bg-white bg-opacity-30 rounded-full text-xs text-white">
                {{ stage.deals_count }}
              </span>
            </div>
            <p class="text-xs text-white text-opacity-75 mt-1">
              {{ formatCurrency(stage.deals_value) }}
            </p>
          </div>

          <!-- Stage Deals -->
          <div class="bg-gray-100 dark:bg-gray-800 rounded-b-lg p-3 space-y-3 min-h-96">
            <div
              v-for="deal in stage.deals"
              :key="deal.id"
              @click="viewDeal(deal)"
              class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow cursor-pointer hover:shadow-md transition-shadow"
            >
              <h4 class="font-medium text-gray-900 dark:text-white">{{ deal.title }}</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ deal.contact.name }}</p>
              <div class="flex justify-between items-center mt-3">
                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(deal.value) }}
                </span>
                <span
                  v-if="deal.expected_close_date"
                  :class="[
                    'text-xs px-2 py-1 rounded',
                    isOverdue(deal) ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ formatDate(deal.expected_close_date) }}
                </span>
              </div>
            </div>

            <button
              @click="createDealInStage(stage)"
              class="w-full py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-500 dark:text-gray-400 hover:border-orbit-500 hover:text-orbit-600"
            >
              + Add Deal
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Deal Modal -->
    <Modal
      :show="showCreateDeal"
      title="Create New Deal"
      @close="showCreateDeal = false"
    >
      <form @submit.prevent="createDeal">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Deal Title
          </label>
          <input
            v-model="form.title"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Contact
          </label>
          <select
            v-model="form.contact_id"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
          >
            <option value="">Select Contact</option>
            <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
              {{ contact.name }}
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Value
          </label>
          <input
            v-model="form.value"
            type="number"
            step="0.01"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Expected Close Date
          </label>
          <input
            v-model="form.expected_close_date"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
          />
        </div>

        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="showCreateDeal = false"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="processing"
            class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700 disabled:opacity-50"
          >
            {{ processing ? 'Creating...' : 'Create Deal' }}
          </button>
        </div>
      </form>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Modal from '@/Components/UI/Modal.vue';

const props = defineProps({
  pipeline: Object,
  contacts: Array,
});

const showCreateDeal = ref(false);
const processing = ref(false);

const form = ref({
  title: '',
  contact_id: '',
  value: '',
  expected_close_date: '',
  pipeline_id: props.pipeline?.id,
  stage_id: props.pipeline?.stages?.[0]?.id,
});

const formatCurrency = (value) => {
  if (!value) return '$0';
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const isOverdue = (deal) => {
  return deal.expected_close_date && new Date(deal.expected_close_date) < new Date();
};

const createDeal = () => {
  processing.value = true;
  router.post('/crm/deals', form.value, {
    onSuccess: () => {
      processing.value = false;
      showCreateDeal.value = false;
      form.value = { title: '', contact_id: '', value: '', expected_close_date: '' };
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const viewDeal = (deal) => {
  router.get(`/crm/deals/${deal.id}`);
};

const createDealInStage = (stage) => {
  form.value.stage_id = stage.id;
  showCreateDeal.value = true;
};
</script>
