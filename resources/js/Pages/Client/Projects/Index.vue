<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FolderIcon, CalendarIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    projects: Array,
});

const getStatusColor = (status) => {
    const colors = {
        'draft': 'bg-gray-100 text-gray-700',
        'active': 'bg-green-100 text-green-700',
        'paused': 'bg-yellow-100 text-yellow-700',
        'completed': 'bg-blue-100 text-blue-700',
        'cancelled': 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Borrador',
        'active': 'Activo',
        'paused': 'Pausado',
        'completed': 'Completado',
        'cancelled': 'Cancelado',
    };
    return labels[status] || status;
};

const formatDate = (date) => {
    if (!date) return 'Sin fecha';
    return new Date(date).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Calculate progress for a project based on completed tasks
const getProgress = (project) => {
    if (!project.stages) return 0;
    const totalTasks = project.stages.reduce((sum, s) => sum + (s.tasks?.length || 0), 0);
    if (totalTasks === 0) return 0;
    const completedTasks = project.stages.reduce((sum, s) => 
        sum + (s.tasks?.filter(t => t.status === 'completed').length || 0), 0);
    return Math.round((completedTasks / totalTasks) * 100);
};
</script>

<template>
    <Head title="Mis Proyectos" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold leading-tight text-gray-900">Mis Proyectos</h2>
                <p class="text-sm text-gray-500 mt-1">Visualiza el estado y progreso de tus proyectos activos.</p>
            </div>
        </template>

        <div class="py-0">
            <!-- No Permission State -->
            <div v-if="!$page.props.auth.user?.permissions?.find(p => p.name === 'view_projects')" class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-gray-100">
                <div class="bg-gray-50 p-6 rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-400">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Acceso Restringido</h3>
                <p class="text-gray-500 mt-1">No tienes permisos para ver los proyectos. Solicita acceso a tu administrador.</p>
            </div>

            <!-- Projects Grid -->
            <div v-else-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link 
                    v-for="project in projects" 
                    :key="project.id"
                    :href="route('portal.projects.show', project.id)"
                    class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-brand/30 transition-all duration-300"
                >
                    <!-- Header with gradient -->
                    <div class="h-2 bg-gradient-to-r from-brand to-blue-400"></div>
                    
                    <div class="p-6">
                        <!-- Title & Status -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-50 p-2 rounded-lg text-brand group-hover:bg-brand group-hover:text-white transition-colors">
                                    <FolderIcon class="h-5 w-5" />
                                </div>
                                <h3 class="font-bold text-gray-900 group-hover:text-brand transition-colors">{{ project.name }}</h3>
                            </div>
                            <span :class="[getStatusColor(project.status), 'text-xs font-medium px-2 py-1 rounded-full']">
                                {{ getStatusLabel(project.status) }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p v-if="project.description" class="text-sm text-gray-500 mb-4 line-clamp-2">
                            {{ project.description }}
                        </p>

                        <!-- Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-gray-500">Progreso</span>
                                <span class="text-xs font-semibold text-brand">{{ getProgress(project) }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div 
                                    class="bg-brand h-2 rounded-full transition-all duration-500"
                                    :style="{ width: getProgress(project) + '%' }"
                                ></div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                            <div class="text-center">
                                <p class="text-lg font-bold text-gray-900">{{ project.stages_count || 0 }}</p>
                                <p class="text-xs text-gray-500">Etapas</p>
                            </div>
                            <div class="text-center">
                                <p class="text-lg font-bold text-gray-900">{{ project.invoices_count || 0 }}</p>
                                <p class="text-xs text-gray-500">Facturas</p>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100 text-xs text-gray-400">
                            <div class="flex items-center gap-1">
                                <CalendarIcon class="h-3.5 w-3.5" />
                                <span>{{ formatDate(project.start_date) }}</span>
                            </div>
                            <span>→</span>
                            <span>{{ formatDate(project.end_date) }}</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-gray-100">
                <div class="bg-blue-50 p-6 rounded-full mb-4">
                    <FolderIcon class="h-12 w-12 text-brand" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">No tienes proyectos asignados</h3>
                <p class="text-gray-500 mt-1">Cuando tu empresa tenga proyectos activos, aparecerán aquí.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
