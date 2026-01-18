<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CloudArrowUpIcon, CloudArrowDownIcon, TrashIcon, ArrowPathIcon, ServerStackIcon, ClockIcon, CircleStackIcon, DocumentArrowDownIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { ref } from 'vue';

const props = defineProps({
    backups: Array,
    lastBackup: Object,
});

const isCreating = ref(false);
const isDeleting = ref(null);

const createBackup = () => {
    isCreating.value = true;
    router.post(route('backups.store'), {}, {
        onFinish: () => {
            isCreating.value = false;
        },
    });
};

const downloadBackup = (backup) => {
    window.location.href = route('backups.download', { path: backup.path });
};

const deleteBackup = (backup) => {
    if (confirm(`¿Estás seguro de eliminar el backup ${backup.name}? Esta acción no se puede deshacer.`)) {
        isDeleting.value = backup.path;
        router.delete(route('backups.destroy', { path: backup.path }), {
            onFinish: () => {
                isDeleting.value = null;
            },
        });
    }
};

const runCleanup = () => {
    if (confirm('¿Ejecutar limpieza de backups antiguos según la política de retención?')) {
        router.post(route('backups.cleanup'));
    }
};

const formatDate = (timestamp) => {
    return new Date(timestamp * 1000).toLocaleString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Backups" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Copias de Seguridad</h2>
                    <p class="text-sm text-gray-500 mt-1">Gestión de backups almacenados en Cloudflare R2.</p>
                </div>
                <div class="flex items-center gap-3 self-start sm:self-auto">
                    <button
                        @click="runCleanup"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        <ArrowPathIcon class="h-5 w-5" />
                        <span class="hidden sm:inline">Limpiar Antiguos</span>
                    </button>
                    <PrimaryButton
                        @click="createBackup"
                        :disabled="isCreating"
                        class="inline-flex items-center gap-2"
                    >
                        <CloudArrowUpIcon class="h-5 w-5" :class="{ 'animate-pulse': isCreating }" />
                        <template v-if="isCreating">Creando...</template>
                        <template v-else>
                            <span class="hidden sm:inline">Crear Backup</span>
                            <span class="sm:hidden">Crear</span>
                        </template>
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-0">
            <!-- Stats Row -->
            <div v-if="lastBackup" class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <ServerStackIcon class="h-5 w-5 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Backups</p>
                            <p class="text-lg font-semibold text-gray-900">{{ backups.length }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Último Backup</p>
                            <p class="text-lg font-semibold text-gray-900">{{ formatDate(lastBackup.last_modified) }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl p-5">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <CircleStackIcon class="h-5 w-5 text-gray-600" />
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Tamaño Último</p>
                            <p class="text-lg font-semibold text-gray-900">{{ lastBackup.size }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backups Table -->
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="backup in backups" :key="backup.path" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-gray-100 rounded-lg">
                                            <DocumentArrowDownIcon class="h-5 w-5 text-gray-600" />
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ backup.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ backup.size }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(backup.last_modified) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-3">
                                        <button
                                            @click="downloadBackup(backup)"
                                            class="text-gray-600 hover:text-gray-900 transition-colors"
                                            title="Descargar"
                                        >
                                            <CloudArrowDownIcon class="h-5 w-5" />
                                        </button>
                                        <button
                                            @click="deleteBackup(backup)"
                                            :disabled="isDeleting === backup.path"
                                            class="text-red-500 hover:text-red-700 transition-colors disabled:opacity-50"
                                            title="Eliminar"
                                        >
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="backups.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <ServerStackIcon class="h-12 w-12 text-gray-300 mb-3" />
                                        <p class="font-medium">No hay backups disponibles</p>
                                        <p class="text-sm mt-1">Crea tu primer backup para empezar.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Note -->
            <div class="mt-4 text-xs text-gray-200 bg-gray-800 p-3 rounded-md">
                Backups automáticos cada 7 días. Retención: 28 días completos, luego semanal por 4 semanas.
            </div>
        </div>
    </AuthenticatedLayout>
</template>
