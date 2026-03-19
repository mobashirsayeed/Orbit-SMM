<template>
  <AppLayout>
    <template #header>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Choose Your Plan</h1>
    </template>

    <div class="max-w-5xl mx-auto">
      <!-- Current Plan Banner -->
      <div v-if="currentSubscription" class="mb-8 bg-orbit-50 dark:bg-gray-800 rounded-lg p-6">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Current Plan</h2>
            <p class="text-3xl font-bold text-orbit-600 mt-1">
              ${{ currentSubscription.plan.price_monthly }}/mo
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ currentSubscription.plan.name }} Plan
            </p>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-500 dark:text-gray-400">Next billing date</p>
            <p class="text-lg font-medium text-gray-900 dark:text-white">
              {{ formatDate(currentSubscription.next_billing_date) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="plan in plans"
          :key="plan.id"
          :class="[
            'bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden',
            plan.recommended ? 'ring-2 ring-orbit-600' : ''
          ]"
        >
          <!-- Plan Header -->
          <div :class="['px-6 py-6', plan.recommended ? 'bg-orbit-600' : 'bg-gray-50 dark:bg-gray-700']">
            <h3 :class="['text-xl font-bold', plan.recommended ? 'text-white' : 'text-gray-900 dark:text-white']">
              {{ plan.name }}
            </h3>
            <div class="mt-4 flex items-baseline">
              <span :class="['text-4xl font-extrabold', plan.recommended ? 'text-white' : 'text-gray-900 dark:text-white']">
                ${{ plan.price_monthly }}
              </span>
              <span :class="['ml-1 text-xl', plan.recommended ? 'text-white text-opacity-75' : 'text-gray-500 dark:text-gray-400']">
                /month
              </span>
            </div>
          </div>

          <!-- Plan Features -->
          <div class="px-6 py-6">
            <ul class="space-y-4">
              <li
                v-for="feature in plan.features"
                :key="feature"
                class="flex items-start"
              >
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ feature }}</span>
              </li>
            </ul>

            <!-- Limits -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
              <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Limits</h4>
              <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li>{{ plan.limits.social_accounts }} Social Accounts</li>
                <li>{{ plan.limits.ai_credits }} AI Credits/mo</li>
                <li>{{ plan.limits.team_members }} Team Members</li>
                <li>{{ plan.limits.scheduled_posts }} Scheduled Posts</li>
              </ul>
            </div>

            <!-- CTA Button -->
            <div class="mt-6">
              <button
                @click="selectPlan(plan)"
                :disabled="currentSubscription?.plan.id === plan.id"
                :class="[
                  'w-full py-3 px-4 rounded-md text-sm font-medium',
                  currentSubscription?.plan.id === plan.id
                    ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                    : plan.recommended
                    ? 'bg-orbit-600 text-white hover:bg-orbit-700'
                    : 'bg-gray-800 dark:bg-gray-600 text-white hover:bg-gray-900 dark:hover:bg-gray-500'
                ]"
              >
                {{ currentSubscription?.plan.id === plan.id ? 'Current Plan' : 'Get Started' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ Section -->
      <div class="mt-16">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-8">
          Frequently Asked Questions
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="faq in faqs"
            :key="faq.question"
            class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow"
          >
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
              {{ faq.question }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ faq.answer }}</p>
          </div>
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
  plans: Array,
  currentSubscription: Object,
});

const faqs = [
  {
    question: 'Can I change plans later?',
    answer: 'Yes, you can upgrade or downgrade your plan at any time. Changes will be prorated.',
  },
  {
    question: 'What payment methods do you accept?',
    answer: 'We accept all major credit cards through our secure Stripe payment processor.',
  },
  {
    question: 'Is there a free trial?',
    answer: 'Yes, all plans come with a 14-day free trial. No credit card required to start.',
  },
  {
    question: 'Can I cancel anytime?',
    answer: 'Absolutely. You can cancel your subscription at any time from your account settings.',
  },
];

const selectPlan = (plan) => {
  router.post('/billing/subscribe', {
    plan_id: plan.id,
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};
</script>
