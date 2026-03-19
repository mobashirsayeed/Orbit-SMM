<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
      <thead class="bg-gray-50 dark:bg-gray-800">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            @click="column.sortable ? sort(column.key) : null"
            :class="[
              'px-6 py-3 text-left text-xs font-medium uppercase tracking-wider',
              column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700' : '',
              'text-gray-500 dark:text-gray-400'
            ]"
          >
            <div class="flex items-center space-x-1">
              <span>{{ column.label }}</span>
              <svg
                v-if="column.sortable && sortBy === column.key"
                :class="['w-4 h-4', sortDirection === 'asc' ? 'transform rotate-180' : '']"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
              </svg>
            </div>
          </th>
          <th v-if="$slots.actions" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
            Actions
          </th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
        <tr v-for="(item, index) in sortedData" :key="item.id || index">
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
          >
            <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]">
              {{ formatValue(item[column.key], column.format) }}
            </slot>
          </td>
          <td v-if="$slots.actions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <slot name="actions" :item="item"></slot>
          </td>
        </tr>
        <tr v-if="sortedData.length === 0">
          <td :colspan="columns.length + 1" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
            <slot name="empty">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p>No data available</p>
              </div>
            </slot>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex justify-between items-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
        </p>
        <div class="flex space-x-2">
          <button
            @click="$emit('page-change', pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm disabled:opacity-50"
          >
            Previous
          </button>
          <button
            @click="$emit('page-change', pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
  pagination: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['page-change', 'sort']);

const sortBy = ref(null);
const sortDirection = ref('asc');

const sortedData = computed(() => {
  if (!sortBy.value) return props.data;

  return [...props.data].sort((a, b) => {
    const aVal = a[sortBy.value];
    const bVal = b[sortBy.value];

    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
});

const sort = (key) => {
  if (sortBy.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = key;
    sortDirection.value = 'asc';
  }
  emit('sort', { key, direction: sortDirection.value });
};

const formatValue = (value, format) => {
  if (value === null || value === undefined) return '-';

  switch (format) {
    case 'date':
      return new Date(value).toLocaleDateString();
    case 'datetime':
      return new Date(value).toLocaleString();
    case 'currency':
      return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
    case 'number':
      return new Intl.NumberFormat().format(value);
    default:
      return value;
  }
};
</script>
