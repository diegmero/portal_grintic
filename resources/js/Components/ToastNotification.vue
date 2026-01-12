<script setup>
import { ref, onMounted, watch } from 'vue';
import { TransitionRoot, TransitionChild } from '@headlessui/vue';
import { BellIcon, XMarkIcon, CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';

const show = ref(false);
const message = ref('');
const title = ref('Notification');
const type = ref('info'); // info, success, error

import { usePage } from '@inertiajs/vue3';

const page = usePage();

onMounted(() => {
    // Only subscribe if user is an admin
    // Check using the shared roles prop
    const isAdmin = page.props.auth.user.roles.some(role => role.name === 'admin');
    
    if (!isAdmin) {
        return;
    }

    // Listen to admin alerts
    // For custom events with broadcastAs(), use dot prefix
    window.Echo.private('admin.alerts')
        .listen('.ClientActivityDetected', (e) => {
            title.value = `Actividad: ${e.client_name}`;
            message.value = e.message;
            type.value = 'info';
            show.value = true;

            setTimeout(() => {
                show.value = false;
            }, 8000);
        });
});

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        title.value = 'Éxito';
        message.value = flash.success;
        type.value = 'success';
        show.value = true;
        setTimeout(() => show.value = false, 5000);
    }
    if (flash?.error) {
        title.value = 'Error';
        message.value = flash.error;
        type.value = 'error';
        show.value = true;
        setTimeout(() => show.value = false, 8000);
    }
}, { deep: true });
</script>

<template>
     <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6" style="z-index: 9999;">
      <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
        <TransitionRoot as="template" :show="show" enter="transform ease-out duration-300 transition" enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to="translate-y-0 opacity-100 sm:translate-x-0" leave="transition ease-in duration-100" leave-from="opacity-100" leave-to="opacity-0">
          <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
            <div class="p-4">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <BellIcon v-if="type === 'info'" class="h-6 w-6 text-brand" aria-hidden="true" />
                  <CheckCircleIcon v-if="type === 'success'" class="h-6 w-6 text-green-500" aria-hidden="true" />
                  <ExclamationCircleIcon v-if="type === 'error'" class="h-6 w-6 text-red-500" aria-hidden="true" />
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                  <p class="text-sm font-medium text-gray-900">{{ title }}</p>
                  <p class="mt-1 text-sm text-gray-500">{{ message }}</p>
                </div>
                <div class="ml-4 flex flex-shrink-0">
                  <button type="button" @click="show = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <span class="sr-only">Close</span>
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </TransitionRoot>
      </div>
    </div>
</template>
