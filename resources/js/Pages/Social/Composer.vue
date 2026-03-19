<template>
  <AppLayout>
    <template #header>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Post</h1>
    </template>

    <div class="max-w-4xl mx-auto">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <!-- Platform Selection -->
        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Select Platforms
          </label>
          <div class="flex space-x-4">
            <button
              v-for="platform in platforms"
              :key="platform.id"
              @click="togglePlatform(platform.id)"
              :class="[
                'flex items-center px-4 py-2 rounded-lg border-2 transition-colors',
                selectedPlatforms.includes(platform.id)
                  ? 'border-' + platform.color + ' bg-' + platform.color + '-50 dark:bg-' + platform.color + '-900'
                  : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'
              ]"
            >
              <component :is="platform.icon" :class="'w-5 h-5 text-' + platform.color + '-600'" />
              <span class="ml-2 text-sm font-medium">{{ platform.name }}</span>
            </button>
          </div>
        </div>

        <!-- Content Editor -->
        <div class="px-6 py-4">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Content
            </label>
            <textarea
              v-model="form.body"
              rows="6"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-orbit-500 focus:border-orbit-500 dark:bg-gray-700 dark:text-white"
              placeholder="What's on your mind?"
            ></textarea>
            <div class="flex justify-between mt-2">
              <span class="text-sm text-gray-500">
                {{ form.body.length }} / {{ characterLimit }} characters
              </span>
              <button
                @click="generateWithAI"
                :disabled="aiGenerating"
                class="text-sm text-orbit-600 hover:text-orbit-500 flex items-center"
              >
                <SparklesIcon v-if="!aiGenerating" class="w-4 h-4 mr-1" />
                <svg v-else class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ aiGenerating ? 'Generating...' : 'Generate with AI' }}
              </button>
            </div>
          </div>

          <!-- Media Upload -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Media
            </label>
            <div
              @drop.prevent="handleDrop"
              @dragover.prevent
              class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-orbit-500 transition-colors"
            >
              <input
                type="file"
                ref="fileInput"
                @change="handleFileSelect"
                accept="image/*"
                multiple
                class="hidden"
              />
              <button
                @click="$refs.fileInput.click()"
                type="button"
                class="text-orbit-600 hover:text-orbit-500 font-medium"
              >
                Click to upload
              </button>
              <span class="text-gray-500"> or drag and drop images here</span>
              <p class="text-xs text-gray-400 mt-1">PNG, JPG, GIF up to 10MB</p>
            </div>

            <!-- Media Preview -->
            <div v-if="mediaFiles.length > 0" class="mt-4 grid grid-cols-4 gap-4">
              <div
                v-for="(file, index) in mediaFiles"
                :key="index"
                class="relative aspect-square rounded-lg overflow-hidden"
              >
                <img :src="file.preview" class="w-full h-full object-cover" />
                <button
                  @click="removeMedia(index)"
                  class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                >
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Scheduling -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Schedule
            </label>
            <div class="flex space-x-4">
              <button
                @click="scheduleType = 'now'"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium',
                  scheduleType === 'now'
                    ? 'bg-orbit-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                ]"
              >
                Publish Now
              </button>
              <button
                @click="scheduleType = 'scheduled'"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium',
                  scheduleType === 'scheduled'
                    ? 'bg-orbit-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                ]"
              >
                Schedule
              </button>
              <button
                @click="scheduleType = 'optimal'"
                :class="[
                  'px-4 py-2 rounded-md text-sm font-medium',
                  scheduleType === 'optimal'
                    ? 'bg-orbit-600 text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                ]"
              >
                Optimal Time
              </button>
            </div>

            <div v-if="scheduleType === 'scheduled'" class="mt-3">
              <input
                v-model="form.scheduled_at"
                type="datetime-local"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-orbit-500 focus:border-orbit-500 dark:bg-gray-700 dark:text-white"
              />
            </div>
          </div>
        </div>

        <!-- Preview -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4">
          <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Preview</h3>
          <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div v-for="platform in selectedPlatforms" :key="platform" class="mb-4">
              <p class="text-xs font-medium text-gray-500 mb-2">{{ getPlatformName(platform) }}</p>
              <div class="bg-white dark:bg-gray-800 rounded-lg p-3">
                <p class="text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{{ form.body }}</p>
                <div v-if="mediaFiles.length > 0" class="mt-3 grid grid-cols-2 gap-2">
                  <img
                    v-for="(file, index) in mediaFiles.slice(0, 2)"
                    :key="index"
                    :src="file.preview"
                    class="rounded-lg w-full h-24 object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-700 rounded-b-lg">
          <div class="flex justify-end space-x-3">
            <button
              @click="saveDraft"
              :disabled="processing"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              Save Draft
            </button>
            <button
              @click="submit"
              :disabled="processing || selectedPlatforms.length === 0"
              class="px-4 py-2 bg-orbit-600 text-white rounded-md text-sm font-medium hover:bg-orbit-700 disabled:opacity-50"
            >
              {{ scheduleType === 'now' ? 'Publish' : 'Schedule' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { SparklesIcon } from '@heroicons/vue/24/outline';

const platforms = [
  { id: 'facebook', name: 'Facebook', color: 'blue', icon: 'FacebookIcon' },
  { id: 'twitter', name: 'Twitter', color: 'gray', icon: 'TwitterIcon' },
  { id: 'linkedin', name: 'LinkedIn', color: 'blue', icon: 'LinkedInIcon' },
  { id: 'instagram', name: 'Instagram', color: 'pink', icon: 'InstagramIcon' },
];

const selectedPlatforms = ref([]);
const scheduleType = ref('now');
const aiGenerating = ref(false);
const processing = ref(false);
const mediaFiles = ref([]);

const form = useForm({
  body: '',
  platforms: [],
  media_urls: [],
  scheduled_at: null,
  status: 'draft',
});

const characterLimit = computed(() => {
  if (selectedPlatforms.value.includes('twitter')) return 280;
  return 5000;
});

const togglePlatform = (platformId) => {
  const index = selectedPlatforms.value.indexOf(platformId);
  if (index > -1) {
    selectedPlatforms.value.splice(index, 1);
  } else {
    selectedPlatforms.value.push(platformId);
  }
  form.platforms = selectedPlatforms.value;
};

const getPlatformName = (platformId) => {
  return platforms.find(p => p.id === platformId)?.name || platformId;
};

const handleFileSelect = (event) => {
  const files = Array.from(event.target.files);
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      mediaFiles.value.push({
        file,
        preview: e.target.result,
      });
    };
    reader.readAsDataURL(file);
  });
};

const handleDrop = (event) => {
  const files = Array.from(event.dataTransfer.files);
  files.forEach(file => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = (e) => {
        mediaFiles.value.push({
          file,
          preview: e.target.result,
        });
      };
      reader.readAsDataURL(file);
    }
  });
};

const removeMedia = (index) => {
  mediaFiles.value.splice(index, 1);
};

const generateWithAI = async () => {
  aiGenerating.value = true;
  try {
    const response = await fetch('/ai/generate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        prompt: 'Generate a social media post',
        type: 'caption',
      }),
    });
    const data = await response.json();
    form.body = data.response;
  } catch (error) {
    console.error('AI generation failed:', error);
  } finally {
    aiGenerating.value = false;
  }
};

const saveDraft = () => {
  form.status = 'draft';
  submit();
};

const submit = () => {
  if (scheduleType.value === 'scheduled') {
    form.status = 'scheduled';
  } else if (scheduleType.value === 'optimal') {
    form.status = 'scheduled';
    // Will be handled by backend optimal time service
  } else {
    form.status = 'publishing';
  }

  processing.value = true;
  form.post('/social/posts', {
    onSuccess: () => {
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};
</script>
