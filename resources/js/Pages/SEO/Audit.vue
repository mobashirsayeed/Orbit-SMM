<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">SEO Audit</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ audit.domain }}</p>
        </div>
        <button
          @click="runAudit"
          :disabled="running"
          class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
        >
          {{ running ? 'Running...' : 'Re-run Audit' }}
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Score Overview -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Overall Score</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Based on {{ audit.urls_crawled || 0 }} pages</p>
          </div>
          <div class="text-center">
            <div class="text-5xl font-bold" :class="scoreColor(audit.seo_score)">
              {{ audit.seo_score || 0 }}
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">out of 100</p>
          </div>
        </div>

        <!-- Score Breakdown -->
        <div class="mt-6 grid grid-cols-4 gap-4">
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ audit.seo_score || 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">SEO</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ audit.performance_score || 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Performance</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ audit.accessibility_score || 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Accessibility</div>
          </div>
          <div class="text-center">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ audit.best_practices_score || 0 }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Best Practices</div>
          </div>
        </div>
      </div>

      <!-- Issues -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Issues Found</h2>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="(issue, index) in audit.issues"
            :key="index"
            class="px-6 py-4"
          >
            <div class="flex items-start space-x-3">
              <svg
                :class="[
                  'w-5 h-5 flex-shrink-0',
                  issue.severity === 'critical' ? 'text-red-500' :
                  issue.severity === 'warning' ? 'text-yellow-500' :
                  'text-blue-500'
                ]"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ issue.message }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Type: {{ issue.type }} | Severity: {{ issue.severity }}</p>
              </div>
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  issue.severity === 'critical' ? 'bg-red-100 text-red-800' :
                  issue.severity === 'warning' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-blue-100 text-blue-800'
                ]"
              >
                {{ issue.severity }}
              </span>
            </div>
          </div>

          <div v-if="!audit.issues || audit.issues.length === 0" class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">No issues found! Great job!</p>
          </div>
        </div>
      </div>

      <!-- Recommendations -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Recommendations</h2>
        </div>

        <div class="px-6 py-4">
          <ul class="space-y-3">
            <li
              v-for="(rec, index) in audit.recommendations"
              :key="index"
              class="flex items-start space-x-3"
            >
              <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm text-gray-700 dark:text-gray-300">{{ rec }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';

const props = defineProps({
  audit: Object,
});

const running = ref(false);

const scoreColor = (score) => {
  if (score >= 80) return 'text-green-600';
  if (score >= 60) return 'text-yellow-600';
  return 'text-red-600';
};

const runAudit = () => {
  running.value = true;
  router.post(`/seo/monitors/${props.audit.id}/crawl`, {}, {
    onFinish: () => {
      running.value = false;
    },
  });
};
</script>
