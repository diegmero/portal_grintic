<script setup>
import { ref, onMounted, computed } from 'vue';
import { BellIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const isAdmin = computed(() => !page.props.auth.user?.company_id);

const showDropdown = ref(false);
const notifications = ref([]);
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

const markAsRead = (id) => {
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
        notification.read = true;
        router.post(route('notifications.read', id), {}, { preserveScroll: true, preserveState: true });
    }
};

const markAllAsRead = () => {
    notifications.value.forEach(n => n.read = true);
    router.post(route('notifications.readAll'), {}, { preserveScroll: true, preserveState: true });
};

const clearAll = () => {
    notifications.value = [];
    router.delete(route('notifications.destroy'), { preserveScroll: true, preserveState: true });
};

const formatTime = (date) => {
    const now = new Date();
    const diffMs = now - new Date(date);
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Ahora';
    if (diffMins < 60) return `Hace ${diffMins}m`;
    
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) return `Hace ${diffHours}h`;
    
    return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
};

onMounted(() => {
    // 1. Load initial notifications from Inertia props
    if (page.props.auth.notifications) {
        notifications.value = page.props.auth.notifications.map(n => ({
            id: n.id,
            title: n.data.title || 'Notificación',
            message: n.data.message || '',
            time: n.created_at,
            read: !!n.read_at,
        }));
    }

    // 2. Listen for Real-time Notifications (Standard Laravel Channel)
    const userId = page.props.auth.user?.id;
    if (userId && window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                notifications.value.unshift({
                    id: notification.id,
                    title: notification.title,
                    message: notification.message,
                    time: new Date().toISOString(),
                    read: false,
                });
                // Limit list size
                if (notifications.value.length > 20) notifications.value.pop();
            });
    }
});
</script>

<template>
    <div class="relative">
        <button 
            type="button" 
            @click="showDropdown = !showDropdown"
            class="p-2.5 text-gray-400 hover:text-gray-500"
        >
            <span class="sr-only">Ver notificaciones</span>
            <div class="relative">
                <BellIcon class="h-6 w-6" aria-hidden="true" />
                
                <!-- Unread Badge -->
                <span 
                    v-if="unreadCount > 0" 
                    class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[16px] h-4 px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full ring-2 ring-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
            </div>
        </button>

        <!-- Dropdown Panel -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div 
                v-if="showDropdown" 
                class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            >
                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                    <div class="flex items-center gap-2">
                        <button 
                            v-if="unreadCount > 0"
                            @click="markAllAsRead" 
                            class="text-xs text-brand hover:text-brand-600"
                        >
                            Marcar todas
                        </button>
                        <button @click="showDropdown = false" class="text-gray-400 hover:text-gray-500">
                            <XMarkIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                    <template v-if="notifications.length > 0">
                        <div 
                            v-for="notification in notifications" 
                            :key="notification.id"
                            @click="markAsRead(notification.id)"
                            :class="[
                                'px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors',
                                !notification.read ? 'bg-brand/5' : ''
                            ]"
                        >
                            <div class="flex items-start gap-3">
                                <div :class="['mt-1 h-2 w-2 rounded-full flex-shrink-0', !notification.read ? 'bg-brand' : 'bg-transparent']"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ notification.title }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-2">{{ notification.message }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.time) }}</p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-else class="px-4 py-8 text-center">
                        <BellIcon class="h-8 w-8 text-gray-300 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">Sin notificaciones</p>
                    </div>
                </div>

                <!-- Footer -->
                <div v-if="notifications.length > 0" class="px-4 py-2 border-t border-gray-100 bg-gray-50">
                    <button @click="clearAll" class="text-xs text-gray-500 hover:text-gray-700 w-full text-center">
                        Limpiar todas
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Click outside to close -->
        <div v-if="showDropdown" class="fixed inset-0 z-40" @click="showDropdown = false"></div>
    </div>
</template>
