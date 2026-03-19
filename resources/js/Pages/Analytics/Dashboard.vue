<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics Dashboard</h1>
        <div class="flex items-center space-x-3">
          <select
            v-model="dateRange.days"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm dark:bg-gray-700 dark:text-white"
          >
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
          </select>
          <button
            @click="syncAnalytics"
            :disabled="syncing"
            class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
          >
            {{ syncing ? 'Syncing...' : 'Sync Data' }}
          </button>
          <button
            @click="exportReport"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Export
          </button>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="metric in overviewMetrics"
          :key="metric.name"
          class="bg-white dark:bg-gray-800 shadow rounded-lg p-6"
        >
          <div class="flex items-center">
            <div :class="`p-3 rounded-lg ${metric.bgColor}`">
              <component :is="metric.icon" :class="`w-6 h-6 ${metric.iconColor}`" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ metric.name }}</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ formatNumber(metric.value) }}
              </p>
              <p :class="`text-sm ${metric.change >= 0 ? 'text-green-600' : 'text-red-600'}`">
                {{ metric.change >= 0 ? '+' : '' }}{{ metric.change }}% from last period
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Follower Growth -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Follower Growth</h3>
          <LineChart :data="followerChartData" :options="chartOptions" />
        </div>

        <!-- Engagement Rate -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Engagement Rate</h3>
          <LineChart :data="engagementChartData" :options="chartOptions" />
        </div>
      </div>

      <!-- Platform Breakdown -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Platform Breakdown</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="(data, platform) in platformBreakdown"
            :key="platform"
            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-medium capitalize text-gray-900 dark:text-white">{{ platform }}</h4>
              <component :is="getPlatformIcon(platform)" :class="`w-6 h-6 ${getPlatformColor(platform)}`" />
            </div>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Followers</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatNumber(data.followers) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Engagement</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ formatNumber(data.engagement) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500 dark:text-gray-400">Posts</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ data.posts }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Posts -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top Performing Posts</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Content
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Platform
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Engagement
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="post in topPosts" :key="post.id">
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">
                  {{ post.body }}
                </td>
                <td class="px-6 py-4 text-sm">
                  <span :class="`px-2 py-1 rounded-full text-xs ${getPlatformBadgeColor(post.platforms[0])}`">
                    {{ post.platforms[0] }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                  {{ formatNumber(post.meta?.engagement || 0) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(post.published_at) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import LineChart from '@/Components/UI/LineChart.vue';
import {
  UserGroupIcon,
  ChartBarIcon,
  EyeIcon,
  HeartIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  metrics: Object,
  topPosts: Array,
  platformBreakdown: Object,
  dateRange: Object,
});

const syncing = ref(false);
const dateRange = ref({
  days: 30,
});

const overviewMetrics = computed(() => [
  {
    name: 'Total Followers',
    value: props.metrics?.total_followers || 0,
    change: 12.5,
    bgColor: 'bg-blue-100',
    iconColor: 'text-blue-600',
    icon: UserGroupIcon,
  },
  {
    name: 'Total Engagement',
    value: props.metrics?.total_engagement || 0,
    change: 8.3,
    bgColor: 'bg-green-100',
    iconColor: 'text-green-600',
    icon: HeartIcon,
  },
  {
    name: 'Impressions',
    value: props.metrics?.total_impressions || 0,
    change: 15.2,
    bgColor: 'bg-purple-100',
    iconColor: 'text-purple-600',
    icon: EyeIcon,
  },
  {
    name: 'Engagement Rate',
    value: `${props.metrics?.engagement_rate || 0}%`,
    change: 3.1,
    bgColor: 'bg-yellow-100',
    iconColor: 'text-yellow-600',
    icon: ChartBarIcon,
  },
]);

const followerChartData = computed(() => ({
  labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
  datasets: [
    {
      label: 'Followers',
       [1000, 1200, 1500, 1800],
      borderColor: '#6c63ff',
      tension: 0.4,
    },
  ],
}));

const engagementChartData = computed(() => ({
  labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
  datasets: [
    {
      label: 'Engagement Rate',
       [2.5, 3.1, 2.8, 3.5],
      borderColor: '#10b981',
      tension: 0.4,
    },
  ],
}));

const chartOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
    },
  },
};

const getPlatformIcon = (platform) => {
  // Return appropriate icon component
  return UserGroupIcon;
};

const getPlatformColor = (platform) => {
  const colors = {
    facebook: 'text-blue-600',
    twitter: 'text-gray-600',
    linkedin: 'text-blue-700',
    instagram: 'text-pink-600',
  };
  return colors[platform] || 'text-gray-600';
};

const getPlatformBadgeColor = (platform) => {
  const colors = {
    facebook: 'bg-blue-100 text-blue-800',
    twitter: 'bg-gray-100 text-gray-800',
    linkedin: 'bg-blue-100 text-blue-800',
    instagram: 'bg-pink-100 text-pink-800',
  };
  return colors[platform] || 'bg-gray-100 text-gray-800';
};

const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M';
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K';
  }
  return num.toString();
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const syncAnalytics = () => {
  syncing.value = true;
  router.get('/analytics/sync', {}, {
    onFinish: () => {
      syncing.value = false;
    },
  });
};

const exportReport = () => {
  router.get('/analytics/export', {
    format: 'csv',
    days: dateRange.value.days,
  });
};
</script>
