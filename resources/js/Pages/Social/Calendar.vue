<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Content Calendar</h1>
        <Link
          href="/social/composer"
          class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
        >
          Create Post
        </Link>
      </div>
    </template>

    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
      <!-- Calendar Controls -->
      <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <button
              @click="changeMonth(-1)"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
              {{ currentMonthName }} {{ currentYear }}
            </h2>
            <button
              @click="changeMonth(1)"
              class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          <!-- Platform Filter -->
          <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-600 dark:text-gray-400">Filter:</label>
            <select
              v-model="selectedPlatform"
              class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-sm dark:bg-gray-700 dark:text-white"
            >
              <option value="">All Platforms</option>
              <option value="facebook">Facebook</option>
              <option value="twitter">Twitter</option>
              <option value="linkedin">LinkedIn</option>
              <option value="instagram">Instagram</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="p-6">
        <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 rounded-lg overflow-hidden">
          <!-- Day Headers -->
          <div
            v-for="day in weekDays"
            :key="day"
            class="bg-gray-50 dark:bg-gray-800 px-3 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300"
          >
            {{ day }}
          </div>

          <!-- Calendar Days -->
          <div
            v-for="day in calendarDays"
            :key="day.date"
            :class="[
              'min-h-32 bg-white dark:bg-gray-800 p-2',
              !day.currentMonth ? 'bg-gray-50 dark:bg-gray-900' : '',
              day.isToday ? 'ring-2 ring-orbit-500' : ''
            ]"
            @dragover.prevent
            @drop="handleDrop($event, day.date)"
          >
            <div class="flex justify-between items-start">
              <span
                :class="[
                  'text-sm font-medium',
                  day.isToday ? 'text-orbit-600' : 'text-gray-700 dark:text-gray-300'
                ]"
              >
                {{ day.day }}
              </span>
              <button
                @click="createPostForDay(day.date)"
                class="text-gray-400 hover:text-orbit-600"
              >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </button>
            </div>

            <!-- Posts for Day -->
            <div class="mt-2 space-y-1">
              <div
                v-for="post in getPostsForDay(day.date)"
                :key="post.id"
                :class="[
                  'px-2 py-1 rounded text-xs cursor-pointer truncate',
                  getPlatformColor(post.platforms[0])
                ]"
                @click="editPost(post)"
                draggable
                @dragstart="handleDragStart($event, post)"
              >
                {{ post.body.substring(0, 30) }}...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
  posts: Array,
});

const currentDate = ref(dayjs());
const selectedPlatform = ref('');
const draggedPost = ref(null);

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const currentMonthName = computed(() => currentDate.value.format('MMMM'));
const currentYear = computed(() => currentDate.value.year());

const calendarDays = computed(() => {
  const startOfMonth = currentDate.value.startOf('month');
  const endOfMonth = currentDate.value.endOf('month');
  const startOfCalendar = startOfMonth.startOf('week');
  const endOfCalendar = endOfMonth.endOf('week');

  const days = [];
  let day = startOfCalendar;

  while (day.isSameOrBefore(endOfCalendar)) {
    days.push({
      date: day.format('YYYY-MM-DD'),
      day: day.date(),
      currentMonth: day.month() === currentDate.value.month(),
      isToday: day.isSame(dayjs(), 'day'),
    });
    day = day.add(1, 'day');
  }

  return days;
});

const changeMonth = (delta) => {
  currentDate.value = currentDate.value.add(delta, 'month');
};

const getPostsForDay = (date) => {
  let filtered = props.posts.filter(post => {
    const postDate = dayjs(post.scheduled_at || post.created_at).format('YYYY-MM-DD');
    return postDate === date;
  });

  if (selectedPlatform.value) {
    filtered = filtered.filter(post => post.platforms.includes(selectedPlatform.value));
  }

  return filtered;
};

const getPlatformColor = (platform) => {
  const colors = {
    facebook: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    twitter: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
    linkedin: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    instagram: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
  };
  return colors[platform] || 'bg-gray-100 text-gray-800';
};

const handleDragStart = (event, post) => {
  draggedPost.value = post;
  event.dataTransfer.setData('text/plain', post.id);
};

const handleDrop = (event, date) => {
  if (draggedPost.value) {
    // Update post scheduled date via API
    console.log('Move post', draggedPost.value.id, 'to', date);
  }
};

const createPostForDay = (date) => {
  window.location.href = `/social/composer?date=${date}`;
};

const editPost = (post) => {
  window.location.href = `/social/posts/${post.id}/edit`;
};
</script>
