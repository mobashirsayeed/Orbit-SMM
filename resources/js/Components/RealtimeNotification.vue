<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const notifications = ref([]);
const pollingInterval = ref(null);

// Poll for new notifications instead of WebSocket (shared hosting compatible)
const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/notifications');
        if (response.data.length > notifications.value.length) {
            notifications.value = response.data;
        }
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
    }
};

onMounted(() => {
    // Poll every 30 seconds (shared hosting friendly)
    fetchNotifications();
    pollingInterval.value = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }
});
</script>

<template>
    <div class="notification-container">
        <div v-for="notification in notifications" :key="notification.id" class="notification">
            {{ notification.message }}
        </div>
    </div>
</template>

<style scoped>
.notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
}

.notification {
    background: white;
    border-left: 4px solid #6c63ff;
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
