<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    CheckCircleIcon,
    ChatBubbleLeftIcon,
    PaperClipIcon,
    ArrowDownTrayIcon,
    ChevronDownIcon,
    ChevronRightIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';
import CommentsSection from '@/Components/Comments/CommentsSection.vue';

const props = defineProps({
    project: Object,
});

// Expanded tasks for viewing details
const expandedTasks = ref({});

const toggleTaskExpansion = (taskId) => {
    expandedTasks.value[taskId] = !expandedTasks.value[taskId];
};

// Calculate overall stats
const totalTasks = props.project.stages?.reduce((sum, s) => sum + s.tasks.length, 0) || 0;
const completedTasks = props.project.stages?.reduce((sum, s) => sum + s.tasks.filter(t => t.status === 'completed').length, 0) || 0;

// Priority display
const priorityLabels = {
    'low': 'Baja',
    'medium': 'Media',
    'high': 'Alta',
    'urgent': 'Urgente',
};

const priorityColors = {
    'low': 'bg-gray-100 text-gray-600',
    'medium': 'bg-blue-100 text-blue-700',
    'high': 'bg-yellow-100 text-yellow-700',
    'urgent': 'bg-red-100 text-red-700',
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold leading-tight text-gray-900">{{ project.name }}</h2>
                <p class="text-sm text-gray-500">Vista de Proyecto (Solo Lectura)</p>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl space-y-6">
                
                <!-- Progress Overview Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Progreso General</h3>
                        <span class="text-2xl font-bold text-brand">{{ project.progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
                        <div class="bg-brand h-3 rounded-full transition-all duration-500" :style="{ width: project.progress + '%' }"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="bg-gray-50 rounded-lg py-3">
                            <p class="text-2xl font-bold text-gray-900">{{ project.stages?.length || 0 }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Etapas</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg py-3">
                            <p class="text-2xl font-bold text-gray-900">{{ totalTasks }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Tareas</p>
                        </div>
                        <div class="bg-green-50 rounded-lg py-3">
                            <p class="text-2xl font-bold text-green-600">{{ completedTasks }}</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Completadas</p>
                        </div>
                    </div>
                </div>

                <!-- Stages & Tasks (Read-Only) -->
                <div v-for="stage in project.stages" :key="stage.id" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-brand rounded-full"></span>
                            <h3 class="font-bold text-gray-900">{{ stage.name }}</h3>
                            <span class="text-xs text-gray-400">({{ stage.tasks.filter(t => t.status === 'completed').length }}/{{ stage.tasks.length }} tareas)</span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50">
                        <div v-for="task in stage.tasks" :key="task.id" class="p-4">
                            <div class="flex items-start gap-3">
                                <!-- Status Icon (Read-Only) -->
                                <div class="mt-0.5">
                                    <CheckCircleIconSolid v-if="task.status === 'completed'" class="h-5 w-5 text-green-500" />
                                    <CheckCircleIcon v-else class="h-5 w-5 text-gray-300" />
                                </div>

                                <div class="flex-1">
                                    <!-- Task Header -->
                                    <div class="flex items-center gap-2">
                                        <button @click="toggleTaskExpansion(task.id)" class="text-gray-400 hover:text-gray-600">
                                            <ChevronDownIcon v-if="expandedTasks[task.id]" class="h-4 w-4" />
                                            <ChevronRightIcon v-else class="h-4 w-4" />
                                        </button>
                                        <h4 :class="['font-medium', task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900']">
                                            {{ task.name }}
                                        </h4>
                                        <span :class="[priorityColors[task.priority] || 'bg-gray-100 text-gray-600', 'text-[10px] font-medium px-1.5 py-0.5 rounded']">
                                            {{ priorityLabels[task.priority] || task.priority }}
                                        </span>
                                    </div>
                                    <p v-if="task.description" class="text-sm text-gray-500 mt-1 ml-6">{{ task.description }}</p>

                                    <!-- Expanded Content: Files & Comments (CLIENT CAN COMMENT) -->
                                    <div v-if="expandedTasks[task.id]" class="mt-4 ml-6 space-y-4">
                                        
                                        <!-- Subtasks (Read-Only) -->
                                        <div v-if="task.subtasks?.length" class="space-y-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Subtareas</p>
                                            <div v-for="subtask in task.subtasks" :key="subtask.id" class="flex items-center gap-2 text-sm">
                                                <CheckCircleIconSolid v-if="subtask.is_completed" class="h-4 w-4 text-green-500" />
                                                <CheckCircleIcon v-else class="h-4 w-4 text-gray-300" />
                                                <span :class="subtask.is_completed ? 'text-gray-400 line-through' : 'text-gray-700'">{{ subtask.name }}</span>
                                            </div>
                                        </div>

                                        <!-- Files (Download Only) -->
                                        <div v-if="task.media?.length" class="space-y-1">
                                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Archivos</p>
                                            <div v-for="file in task.media" :key="file.id" class="flex items-center gap-2 text-sm">
                                                <PaperClipIcon class="h-4 w-4 text-gray-400" />
                                                <a :href="file.original_url" target="_blank" class="text-brand hover:underline">{{ file.file_name }}</a>
                                                <a :href="file.original_url" download class="p-1 text-gray-400 hover:text-brand">
                                                    <ArrowDownTrayIcon class="h-4 w-4" />
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Comments (Client CAN Add) -->
                                        <div class="pt-2 border-t border-gray-100">
                                            <CommentsSection 
                                                :commentable-id="task.id" 
                                                commentable-type="App\Models\Task" 
                                                :initial-comments="task.comments"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Stats -->
                                <div class="flex items-center gap-3 text-gray-400 text-xs">
                                    <span v-if="task.comments?.length" class="flex items-center gap-1">
                                        <ChatBubbleLeftIcon class="h-4 w-4" /> {{ task.comments.length }}
                                    </span>
                                    <span v-if="task.media?.length" class="flex items-center gap-1">
                                        <PaperClipIcon class="h-4 w-4" /> {{ task.media.length }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div v-if="stage.tasks.length === 0" class="p-8 text-center text-gray-400 text-sm">
                            No hay tareas en esta etapa.
                        </div>
                    </div>
                </div>

                <!-- No Stages -->
                <div v-if="!project.stages?.length" class="text-center py-16 bg-white rounded-xl border border-gray-100">
                    <p class="text-gray-400">Este proyecto aún no tiene estructura definida.</p>
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
