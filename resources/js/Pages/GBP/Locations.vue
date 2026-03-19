<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Google Business Profile</h1>
        <button
          @click="syncLocations"
          :disabled="syncing"
          class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
        >
          {{ syncing ? 'Syncing...' : 'Sync Locations' }}
        </button>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Locations Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="location in locations"
          :key="location.id"
          class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ location.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ location.full_address }}</p>
              </div>
              <span
                :class="[
                  'px-2 py-1 rounded-full text-xs font-medium',
                  location.unresponded_reviews_count > 0
                    ? 'bg-red-100 text-red-800'
                    : 'bg-green-100 text-green-800'
                ]"
              >
                {{ location.unresponded_reviews_count }} pending reviews
              </span>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Average Rating</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ location.average_rating?.toFixed(1) || 'N/A' }}
                  <span class="text-yellow-500">★</span>
                </p>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Reviews</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ location.reviews_count || 0 }}
                </p>
              </div>
            </div>

            <div class="mt-4 flex space-x-2">
              <Link
                :href="`/google-business/locations/${location.id}`"
                class="flex-1 px-3 py-2 bg-orbit-600 text-white text-sm rounded-md text-center hover:bg-orbit-700"
              >
                Manage
              </Link>
              <button
                @click="viewReviews(location)"
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm rounded-md text-center hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                Reviews
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="locations.length === 0" class="col-span-full">
          <EmptyState
            title="No Locations Connected"
            description="Connect your Google Business Profile locations to manage posts and reviews"
          >
            <template #action>
              <button
                @click="connectGBP"
                class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700"
              >
                Connect Google Business
              </button>
            </template>
          </EmptyState>
        </div>
      </div>

      <!-- Recent Reviews -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white">Recent Reviews</h2>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="review in recentReviews"
            :key="review.id"
            class="px-6 py-4"
          >
            <div class="flex items-start space-x-4">
              <img
                :src="review.reviewer_avatar || '/default-avatar.png'"
                class="w-10 h-10 rounded-full"
              />
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ review.reviewer_name }}</p>
                    <div class="flex items-center mt-1">
                      <span
                        v-for="star in 5"
                        :key="star"
                        :class="star <= review.rating ? 'text-yellow-500' : 'text-gray-300'"
                      >
                        ★
                      </span>
                      <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ new Date(review.review_date).toLocaleDateString() }}
                      </span>
                    </div>
                  </div>
                  <span
                    v-if="!review.is_responded"
                    class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full"
                  >
                    Needs Response
                  </span>
                </div>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">{{ review.comment }}</p>

                <div v-if="review.is_responded" class="mt-3 pl-4 border-l-2 border-gray-200 dark:border-gray-700">
                  <p class="text-xs text-gray-500 dark:text-gray-400">Your response:</p>
                  <p class="text-sm text-gray-700 dark:text-gray-300">{{ review.reply }}</p>
                </div>

                <div v-else class="mt-3">
                  <button
                    @click="replyToReview(review)"
                    class="text-sm text-orbit-600 hover:text-orbit-500"
                  >
                    Reply to review
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
  locations: Array,
  recentReviews: Array,
});

const syncing = ref(false);

const syncLocations = () => {
  syncing.value = true;
  router.post('/google-business/locations/sync', {}, {
    onFinish: () => {
      syncing.value = false;
    },
  });
};

const connectGBP = () => {
  // OAuth flow for Google Business Profile
  window.location.href = '/auth/google-business/redirect';
};

const viewReviews = (location) => {
  router.get(`/google-business/locations/${location.id}/reviews`);
};

const replyToReview = (review) => {
  router.get(`/google-business/reviews/${review.id}/reply`);
};
</script>
