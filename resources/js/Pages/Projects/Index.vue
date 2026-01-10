<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FolderIcon, PlusIcon, EyeIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';
import CreateProjectSlideOver from '@/Components/Projects/CreateProjectSlideOver.vue';

const props = defineProps({
    projects: Array,
    companies: Array,
});

const showCreateSlideOver = ref(false);

// Status badge colors
const statusColors = {
    'active': 'bg-green-100 text-green-700',
    'on_hold': 'bg-yellow-100 text-yellow-700',
    'completed': 'bg-blue-100 text-blue-700',
    'archived': 'bg-gray-100 text-gray-600',
    'cancelled': 'bg-red-100 text-red-700',
};

const statusLabels = {
    'active': 'Activo',
    'on_hold': 'En Pausa',
    'completed': 'Completado',
    'archived': 'Archivado',
    'cancelled': 'Cancelado',
};

// Date formatter
const formatDate = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Proyectos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Proyectos</h2>
                    <p class="text-sm text-gray-500 mt-1">Gestión de proyectos, etapas y tareas.</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Table -->
                <div v-if="projects.length > 0" class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Proyecto</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cliente</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Progreso</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fechas</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="project in projects" :key="project.id" class="hover:bg-gray-50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-brand/10 flex items-center justify-center">
                                            <FolderIcon class="h-5 w-5 text-brand" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ project.name }}</div>
                                            <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ project.description || 'Sin descripción' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <span v-if="project.company" class="font-medium text-gray-700">{{ project.company.name }}</span>
                                    <span v-else class="italic text-gray-400">Sin cliente</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span :class="[statusColors[project.status] || 'bg-gray-100 text-gray-600', 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']">
                                        {{ statusLabels[project.status] || project.status }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-brand h-2 rounded-full transition-all" :style="{ width: project.progress + '%' }"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">{{ project.progress }}%</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="text-xs">
                                        <div v-if="project.start_date">Inicio: {{ formatDate(project.start_date) }}</div>
                                        <div v-if="project.due_date" class="text-gray-400">Entrega: {{ formatDate(project.due_date) }}</div>
                                        <div v-if="!project.start_date && !project.due_date" class="text-gray-400 italic">Sin fechas</div>
                                    </div>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <Link :href="route('projects.show', project.id)" class="inline-flex items-center gap-1 text-brand hover:text-brand/80 transition-colors">
                                        <EyeIcon class="h-4 w-4" />
                                        <span>Ver</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="bg-blue-50 rounded-full p-6 mx-auto w-fit mb-4">
                        <FolderIcon class="h-12 w-12 text-brand" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">No hay proyectos</h3>
                    <p class="mt-1 text-sm text-gray-500">Crea tu primer proyecto para comenzar a gestionar.</p>
                    <div class="mt-6">
                        <PrimaryButton @click="showCreateSlideOver = true">
                            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                            Crear Proyecto
                        </PrimaryButton>
                    </div>
                </div>

            </div>
        </div>

        <CreateProjectSlideOver :open="showCreateSlideOver" :companies="companies" @close="showCreateSlideOver = false" />

    </AuthenticatedLayout>
</template>
