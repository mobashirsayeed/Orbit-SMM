<template>
  <AppLayout>
    <template #header>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Studio</h1>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Generation Panel -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Content Generation</h2>

          <!-- Type Selection -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              What do you want to create?
            </label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <button
                v-for="type in generationTypes"
                :key="type.id"
                @click="selectedType = type.id"
                :class="[
                  'p-3 rounded-lg border-2 text-center transition-colors',
                  selectedType === type.id
                    ? 'border-orbit-600 bg-orbit-50 dark:bg-orbit-900'
                    : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'
                ]"
              >
                <component :is="type.icon" class="w-6 h-6 mx-auto mb-2 text-orbit-600" />
                <p class="text-sm font-medium">{{ type.name }}</p>
              </button>
            </div>
          </div>

          <!-- Prompt Input -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Describe what you need
            </label>
            <textarea
              v-model="prompt"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
              placeholder="e.g., Write a caption for a product launch post about our new eco-friendly water bottle"
            ></textarea>
          </div>

          <!-- Brand Voice -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Brand Voice
            </label>
            <select
              v-model="brandVoice"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white"
            >
              <option value="">Default</option>
              <option v-for="voice in brandVoices" :key="voice.id" :value="voice.id">
                {{ voice.name }} ({{ voice.tone }})
              </option>
            </select>
          </div>

          <!-- Generate Button -->
          <button
            @click="generateContent"
            :disabled="generating || !prompt.trim()"
            class="w-full px-4 py-2 bg-orbit-600 text-white rounded-md font-medium hover:bg-orbit-700 disabled:opacity-50 flex items-center justify-center"
          >
            <SparklesIcon v-if="!generating" class="w-5 h-5 mr-2" />
            <svg v-else class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ generating ? 'Generating...' : 'Generate' }}
          </button>
        </div>

        <!-- Generated Content -->
        <div v-if="generatedContent" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Generated Content</h2>
            <div class="flex space-x-2">
              <button
                @click="copyToClipboard"
                class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                Copy
              </button>
              <button
                @click="useInPost"
                class="px-3 py-1 text-sm bg-orbit-600 text-white rounded hover:bg-orbit-700"
              >
                Use in Post
              </button>
            </div>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ generatedContent }}</p>
          </div>

          <!-- Hashtag Suggestions -->
          <div v-if="hashtags.length > 0" class="mt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Suggested Hashtags</h3>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="tag in hashtags"
                :key="tag"
                class="px-2 py-1 bg-orbit-100 dark:bg-orbit-900 text-orbit-800 dark:text-orbit-200 text-sm rounded"
              >
                #{{ tag }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Credits -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">AI Credits</h3>
          <div class="mb-4">
            <div class="flex justify-between text-sm mb-1">
              <span class="text-gray-600 dark:text-gray-400">Used</span>
              <span class="font-medium">{{ creditsUsed }} / {{ creditsTotal }}</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div
                class="bg-orbit-600 h-2 rounded-full"
                :style="{ width: `${(creditsUsed / creditsTotal) * 100}%` }"
              ></div>
            </div>
          </div>
          <Link
            href="/billing"
            class="text-sm text-orbit-600 hover:text-orbit-500 font-medium"
          >
            Upgrade plan for more credits →
          </Link>
        </div>

        <!-- Templates -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Templates</h3>
          <div class="space-y-2">
            <button
              v-for="template in templates"
              :key="template.id"
              @click="loadTemplate(template)"
              class="w-full text-left px-3 py-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <p class="text-sm font-medium">{{ template.name }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ template.category }}</p>
            </button>
          </div>
        </div>

        <!-- History -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Generations</h3>
          <div class="space-y-2">
            <div
              v-for="item in history"
              :key="item.id"
              class="p-2 border border-gray-200 dark:border-gray-700 rounded"
            >
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.type }}</p>
              <p class="text-sm truncate">{{ item.prompt }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatDate(item.created_at) }}</p>
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
import { SparklesIcon, DocumentTextIcon, ChatBubbleLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  brandVoices: Array,
  templates: Array,
  history: Array,
  creditsUsed: Number,
  creditsTotal: Number,
});

const selectedType = ref('caption');
const prompt = ref('');
const brandVoice = ref('');
const generating = ref(false);
const generatedContent = ref('');
const hashtags = ref([]);

const generationTypes = [
  { id: 'caption', name: 'Social Caption', icon: ChatBubbleLeftIcon },
  { id: 'blog', name: 'Blog Post', icon: DocumentTextIcon },
  { id: 'image', name: 'AI Image', icon: PhotoIcon },
  { id: 'variation', name: 'Content Variation', icon: SparklesIcon },
];

const generateContent = async () => {
  generating.value = true;
  try {
    const response = await fetch('/ai/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        prompt: prompt.value,
        type: selectedType.value,
        brand_voice: brandVoice.value,
      }),
    });
    const data = await response.json();
    generatedContent.value = data.response;
    hashtags.value = data.hashtags || [];
  } catch (error) {
    console.error('Generation failed:', error);
  } finally {
    generating.value = false;
  }
};

const loadTemplate = (template) => {
  prompt.value = template.prompt_template;
};

const copyToClipboard = () => {
  navigator.clipboard.writeText(generatedContent.value);
};

const useInPost = () => {
  router.get('/social/composer', {
    content: generatedContent.value,
    hashtags: hashtags.value.join(' '),
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};
</script>
