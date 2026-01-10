<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    CheckCircleIcon as CheckCircleIconOutline, 
    PlusIcon, 
    TrashIcon, 
    ChevronDownIcon,
    ChevronRightIcon,
    XMarkIcon,
    PencilSquareIcon,
    PaperClipIcon,
    ArrowUpTrayIcon,
    ArrowDownTrayIcon,
    ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';
import CommentsSection from '@/Components/Comments/CommentsSection.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    project: Object,
});

// Stats
const totalTasks = computed(() => props.project.stages?.reduce((sum, s) => sum + s.tasks.length, 0) || 0);
const completedTasks = computed(() => props.project.stages?.reduce((sum, s) => sum + s.tasks.filter(t => t.status === 'completed').length, 0) || 0);

// Calculate task progress based on subtasks
const getTaskProgress = (task) => {
    if (task.subtasks?.length > 0) {
        const completed = task.subtasks.filter(s => s.is_completed).length;
        return Math.round((completed / task.subtasks.length) * 100);
    }
    return task.status === 'completed' ? 100 : 0;
};

// Calculate stage progress (simple average - all tasks equal)
const getStageProgress = (stage) => {
    if (!stage.tasks?.length) return 0;
    
    let totalProgress = 0;
    stage.tasks.forEach(task => {
        totalProgress += getTaskProgress(task);
    });
    
    return Math.round(totalProgress / stage.tasks.length);
};

// Date formatter
const formatDate = (dateString) => {
    if (!dateString) return '—';
    return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Status config
const statusConfig = {
    'active': { label: 'Activo', class: 'bg-green-500' },
    'on_hold': { label: 'En Pausa', class: 'bg-yellow-500' },
    'completed': { label: 'Completado', class: 'bg-blue-500' },
    'in_progress': { label: 'En Progreso', class: 'bg-blue-400' },
    'pending': { label: 'Pendiente', class: 'bg-gray-400' },
};

// --- Stage Management ---
const showAddStageModal = ref(false);
const showEditStageModal = ref(false);
const stageForm = useForm({ name: '' });
const editingStage = ref(null);

const addStage = () => {
    stageForm.post(route('stages.store', props.project.id), {
        onSuccess: () => { stageForm.reset(); showAddStageModal.value = false; },
        preserveScroll: true,
    });
};

const openEditStageModal = (stage) => {
    editingStage.value = stage;
    stageForm.name = stage.name;
    showEditStageModal.value = true;
};

const updateStage = () => {
    stageForm.put(route('stages.update', editingStage.value.id), {
        onSuccess: () => { stageForm.reset(); showEditStageModal.value = false; editingStage.value = null; },
        preserveScroll: true,
    });
};

const deleteStage = (stageId) => {
    if (confirm('¿Eliminar esta etapa y todas sus tareas?')) {
        router.delete(route('stages.destroy', stageId), { preserveScroll: true });
    }
};

// File Upload for Stage
const uploadFileToStage = (stageId, event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    router.post(route('stages.media.store', stageId), { file }, {
        forceFormData: true,
        preserveScroll: true,
    });
    event.target.value = '';
};

const deleteStageFile = (stageId, mediaId) => {
    if (confirm('¿Eliminar este archivo?')) {
        router.delete(route('stages.media.destroy', { stage: stageId, media: mediaId }), { preserveScroll: true });
    }
};

// --- Task Management ---
const showAddTaskModal = ref(false);
const showEditTaskModal = ref(false);
const activeStageIdForTask = ref(null);
const editingTask = ref(null);
const taskForm = useForm({ name: '', description: '', priority: 'medium', has_subtasks: true });

// Priority config
const priorityConfig = {
    'low': { label: 'Baja', class: 'bg-gray-100 text-gray-600' },
    'medium': { label: 'Media', class: 'bg-blue-100 text-blue-700' },
    'high': { label: 'Alta', class: 'bg-orange-100 text-orange-700' },
    'urgent': { label: 'Urgente', class: 'bg-red-100 text-red-700' },
};

const openAddTaskModal = (stageId) => {
    activeStageIdForTask.value = stageId;
    taskForm.reset();
    taskForm.priority = 'medium';
    taskForm.has_subtasks = true;
    showAddTaskModal.value = true;
};

const addTask = () => {
    taskForm.post(route('tasks.store', activeStageIdForTask.value), {
        onSuccess: () => { taskForm.reset(); showAddTaskModal.value = false; },
        preserveScroll: true,
    });
};

const openEditTaskModal = (task) => {
    editingTask.value = task;
    taskForm.name = task.name;
    taskForm.description = task.description || '';
    taskForm.priority = task.priority || 'medium';
    taskForm.has_subtasks = task.has_subtasks !== false;
    showEditTaskModal.value = true;
};

const updateTask = () => {
    taskForm.patch(route('tasks.update', editingTask.value.id), {
        onSuccess: () => { taskForm.reset(); showEditTaskModal.value = false; editingTask.value = null; },
        preserveScroll: true,
    });
};

const toggleTask = (task) => {
    router.patch(route('tasks.update', task.id), {
        is_completed: task.status !== 'completed',
    }, { preserveScroll: true });
};

const deleteTask = (taskId) => {
    if (confirm('¿Eliminar esta tarea?')) {
        router.delete(route('tasks.destroy', taskId), { preserveScroll: true });
    }
};

// --- Subtask Management ---
const expandedTasks = ref({});
const newSubtaskName = ref({});
const editingSubtask = ref(null);
const editSubtaskName = ref('');

const toggleTaskExpansion = (taskId) => {
    expandedTasks.value[taskId] = !expandedTasks.value[taskId];
};

const addSubtask = (taskId) => {
    if (!newSubtaskName.value[taskId]?.trim()) return;
    router.post(route('subtasks.store', taskId), { name: newSubtaskName.value[taskId] }, {
        preserveScroll: true,
        onSuccess: () => { newSubtaskName.value[taskId] = ''; },
    });
};

const startEditSubtask = (subtask) => {
    editingSubtask.value = subtask.id;
    editSubtaskName.value = subtask.name;
};

const saveSubtaskEdit = (subtaskId) => {
    if (!editSubtaskName.value.trim()) return;
    router.patch(route('subtasks.update', subtaskId), { name: editSubtaskName.value }, {
        preserveScroll: true,
        onSuccess: () => { editingSubtask.value = null; editSubtaskName.value = ''; },
    });
};

const toggleSubtask = (subtask) => {
    router.patch(route('subtasks.update', subtask.id), { is_completed: !subtask.is_completed }, { preserveScroll: true });
};

const deleteSubtask = (subtaskId) => {
    router.delete(route('subtasks.destroy', subtaskId), { preserveScroll: true });
};
</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('projects.index')" class="text-gray-400 hover:text-gray-600">Proyectos</Link>
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-900">{{ project.name }}</span>
            </div>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl">
                <div class="flex gap-6">
                    
                    <!-- Sidebar - Project Info (Compact) -->
                    <div class="w-64 flex-shrink-0">
                        <div class="bg-white rounded-lg border border-gray-200 sticky top-4">
                            <!-- Status Header -->
                            <div class="p-3 border-b border-gray-100">
                                <div class="flex items-center gap-2 mb-2">
                                    <span :class="[statusConfig[project.status]?.class || 'bg-gray-500', 'w-2 h-2 rounded-full']"></span>
                                    <span class="text-xs font-medium text-gray-700">{{ statusConfig[project.status]?.label || project.status }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>Progreso</span>
                                    <span class="font-semibold text-gray-900">{{ project.progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-brand h-1.5 rounded-full" :style="{ width: project.progress + '%' }"></div>
                                </div>
                            </div>

                            <!-- Info List -->
                            <div class="p-3 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Cliente</span>
                                    <span class="text-gray-900 font-medium truncate max-w-[120px]">{{ project.company?.name || '—' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Inicio</span>
                                    <span class="text-gray-900">{{ formatDate(project.start_date) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Entrega</span>
                                    <span class="text-gray-900">{{ formatDate(project.due_date) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Etapas</span>
                                    <span class="text-gray-900">{{ project.stages?.length || 0 }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Tareas</span>
                                    <span class="text-gray-900">{{ completedTasks }}/{{ totalTasks }}</span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div v-if="project.description" class="p-3 border-t border-gray-100">
                                <p class="text-[10px] text-gray-400 uppercase tracking-wide mb-1">Descripción</p>
                                <p class="text-xs text-gray-700 leading-relaxed">{{ project.description }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <ClipboardDocumentListIcon class="h-5 w-5 text-gray-400" />
                                Etapas del Proyecto
                            </h2>
                            <button @click="showAddStageModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-brand hover:bg-brand/90 rounded-lg transition-colors">
                                <PlusIcon class="h-4 w-4" />
                                Nueva Etapa
                            </button>
                        </div>

                        <!-- Stages -->
                        <div class="space-y-4">
                            <div v-for="stage in project.stages" :key="stage.id" class="bg-white rounded-lg border border-gray-200">
                                <!-- Stage Header -->
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span :class="[statusConfig[stage.status]?.class || 'bg-gray-400', 'w-2 h-5 rounded-full']"></span>
                                            <div>
                                                <h3 class="font-semibold text-gray-900 text-sm">{{ stage.name }}</h3>
                                                <span class="text-[10px] text-gray-400">{{ stage.tasks.filter(t => t.status === 'completed').length }}/{{ stage.tasks.length }} tareas</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <!-- Stage Progress -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-24 bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-brand h-1.5 rounded-full transition-all" 
                                                        :style="{ width: getStageProgress(stage) + '%' }"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-600 w-8">
                                                    {{ getStageProgress(stage) }}%
                                                </span>
                                            </div>
                                            <!-- Actions -->
                                            <div class="flex items-center gap-1">
                                                <button @click="openAddTaskModal(stage.id)" class="p-1.5 text-gray-400 hover:text-brand rounded" title="Agregar Tarea">
                                                    <PlusIcon class="h-4 w-4" />
                                                </button>
                                                <label class="p-1.5 text-gray-400 hover:text-brand rounded cursor-pointer" title="Subir Archivo">
                                                    <ArrowUpTrayIcon class="h-4 w-4" />
                                                    <input type="file" class="hidden" @change="uploadFileToStage(stage.id, $event)" />
                                                </label>
                                                <button @click="openEditStageModal(stage)" class="p-1.5 text-gray-400 hover:text-brand rounded" title="Editar Etapa">
                                                    <PencilSquareIcon class="h-4 w-4" />
                                                </button>
                                                <button @click="deleteStage(stage.id)" class="p-1.5 text-gray-400 hover:text-red-500 rounded" title="Eliminar">
                                                    <TrashIcon class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Stage Files -->
                                <div v-if="stage.media?.length" class="px-4 py-2 bg-blue-50/50 border-b border-gray-100">
                                    <div class="flex flex-wrap gap-2">
                                        <div v-for="file in stage.media" :key="file.id" class="inline-flex items-center gap-1.5 bg-white px-2 py-1 rounded border border-gray-200 text-xs group">
                                            <PaperClipIcon class="h-3 w-3 text-gray-400" />
                                            <a :href="file.original_url" target="_blank" class="text-brand hover:underline max-w-[150px] truncate">{{ file.file_name }}</a>
                                            <button @click="deleteStageFile(stage.id, file.id)" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100">
                                                <XMarkIcon class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tasks List -->
                                <div class="divide-y divide-gray-50">
                                    <div v-for="task in stage.tasks" :key="task.id">
                                        <!-- Task Row -->
                                        <div class="px-4 py-2 hover:bg-gray-50/50 flex items-center gap-3 group">
                                            <button @click="toggleTask(task)" class="flex-shrink-0">
                                                <CheckCircleIconSolid v-if="task.status === 'completed'" class="h-5 w-5 text-green-500" />
                                                <CheckCircleIconOutline v-else class="h-5 w-5 text-gray-300 hover:text-green-400" />
                                            </button>
                                            <button @click="toggleTaskExpansion(task.id)" class="text-gray-400 hover:text-gray-600">
                                                <ChevronDownIcon v-if="expandedTasks[task.id]" class="h-3.5 w-3.5" />
                                                <ChevronRightIcon v-else class="h-3.5 w-3.5" />
                                            </button>
                                            <div class="flex-1 min-w-0">
                                                <span :class="['text-sm', task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900']">{{ task.name }}</span>
                                                <span v-if="task.subtasks?.length" class="ml-2 text-[10px] text-gray-400">
                                                    ({{ task.subtasks.filter(s => s.is_completed).length }}/{{ task.subtasks.length }} subtareas)
                                                </span>
                                            </div>
                                            <span :class="[priorityConfig[task.priority]?.class || 'bg-gray-100 text-gray-600', 'text-[10px] font-medium px-1.5 py-0.5 rounded']">
                                                {{ priorityConfig[task.priority]?.label || task.priority }}
                                            </span>
                                            <button @click="openEditTaskModal(task)" class="p-1 text-gray-300 hover:text-brand opacity-0 group-hover:opacity-100">
                                                <PencilSquareIcon class="h-3.5 w-3.5" />
                                            </button>
                                            <button @click="deleteTask(task.id)" class="p-1 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100">
                                                <TrashIcon class="h-3.5 w-3.5" />
                                            </button>
                                        </div>

                                        <!-- Expanded Task Details -->
                                        <div v-if="expandedTasks[task.id]" class="border-t border-gray-100 bg-gradient-to-b from-gray-50 to-white">
                                            <div class="p-4 space-y-4">
                                                
                                                <!-- Description Section -->
                                                <div v-if="task.description" class="bg-white rounded-lg border border-gray-100 p-3">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h12" />
                                                        </svg>
                                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Descripción</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700 leading-relaxed pl-6">{{ task.description }}</p>
                                                </div>
                                                
                                                <!-- Subtasks Section (only if has_subtasks is enabled) -->
                                                <div v-if="task.has_subtasks" class="bg-white rounded-lg border border-gray-100 p-3">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                            </svg>
                                                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Subtareas</span>
                                                        </div>
                                                        <span v-if="task.subtasks?.length" class="text-xs text-gray-400">
                                                            {{ task.subtasks.filter(s => s.is_completed).length }} de {{ task.subtasks.length }}
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Subtask Progress Bar -->
                                                    <div v-if="task.subtasks?.length" class="mb-3 px-1">
                                                        <div class="w-full bg-gray-100 rounded-full h-1">
                                                            <div class="bg-brand h-1 rounded-full transition-all" 
                                                                :style="{ width: (task.subtasks.filter(s => s.is_completed).length / task.subtasks.length * 100) + '%' }"></div>
                                                        </div>
                                                    </div>

                                                    <!-- Subtask List -->
                                                    <div class="space-y-1.5 pl-1">
                                                        <div v-for="subtask in task.subtasks" :key="subtask.id" 
                                                            class="flex items-center gap-2.5 py-1.5 px-2 rounded-md hover:bg-gray-50 group/sub transition-colors">
                                                            <button @click="toggleSubtask(subtask)" class="flex-shrink-0">
                                                                <CheckCircleIconSolid v-if="subtask.is_completed" class="h-4 w-4 text-green-500" />
                                                                <CheckCircleIconOutline v-else class="h-4 w-4 text-gray-300 hover:text-green-400" />
                                                            </button>
                                                            <template v-if="editingSubtask === subtask.id">
                                                                <input v-model="editSubtaskName" @keyup.enter="saveSubtaskEdit(subtask.id)" @blur="saveSubtaskEdit(subtask.id)" 
                                                                    class="text-sm border border-brand rounded px-2 py-0.5 flex-1 focus:ring-1 focus:ring-brand" autofocus />
                                                            </template>
                                                            <template v-else>
                                                                <span :class="['text-sm flex-1', subtask.is_completed ? 'text-gray-400 line-through' : 'text-gray-700']">{{ subtask.name }}</span>
                                                                <div class="flex items-center gap-1 opacity-0 group-hover/sub:opacity-100 transition-opacity">
                                                                    <button @click="startEditSubtask(subtask)" class="p-1 text-gray-400 hover:text-brand rounded">
                                                                        <PencilSquareIcon class="h-3 w-3" />
                                                                    </button>
                                                                    <button @click="deleteSubtask(subtask.id)" class="p-1 text-gray-400 hover:text-red-500 rounded">
                                                                        <XMarkIcon class="h-3 w-3" />
                                                                    </button>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        
                                                        <!-- Add Subtask Input -->
                                                        <div class="flex items-center gap-2.5 py-1.5 px-2">
                                                            <PlusIcon class="h-4 w-4 text-gray-300 flex-shrink-0" />
                                                            <input v-model="newSubtaskName[task.id]" @keyup.enter="addSubtask(task.id)" type="text" 
                                                                placeholder="Agregar subtarea..." 
                                                                class="text-sm border-0 bg-transparent py-0.5 px-0 placeholder-gray-400 focus:ring-0 flex-1" />
                                                        </div>
                                                    </div>

                                                    <div v-if="!task.subtasks?.length" class="text-center py-3 text-xs text-gray-400">
                                                        Sin subtareas aún
                                                    </div>
                                                </div>

                                                <!-- Comments Section -->
                                                <div class="bg-white rounded-lg border border-gray-100 p-3">
                                                    <div class="flex items-center gap-2 mb-3">
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                        </svg>
                                                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Comentarios</span>
                                                        <span v-if="task.comments?.length" class="text-xs text-gray-400">({{ task.comments.length }})</span>
                                                    </div>
                                                    <CommentsSection :commentable-id="task.id" commentable-type="App\Models\Task" :initial-comments="task.comments" />
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="stage.tasks.length === 0" class="px-4 py-4 text-center text-xs text-gray-400">
                                        Sin tareas. <button @click="openAddTaskModal(stage.id)" class="text-brand hover:underline">Crear primera tarea</button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="project.stages?.length === 0" class="text-center py-12 bg-white rounded-lg border border-gray-200">
                                <p class="text-gray-500 mb-2 text-sm">No hay etapas definidas.</p>
                                <button @click="showAddStageModal = true" class="text-brand hover:underline text-sm">+ Crear primera etapa</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Stage Modal -->
        <Modal :show="showAddStageModal" @close="showAddStageModal = false">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Nueva Etapa</h3>
                <form @submit.prevent="addStage">
                    <TextInput v-model="stageForm.name" type="text" class="w-full" placeholder="Nombre de la etapa" required autofocus />
                    <div class="mt-4 flex justify-end gap-2">
                        <SecondaryButton @click="showAddStageModal = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="stageForm.processing">Crear</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Stage Modal -->
        <Modal :show="showEditStageModal" @close="showEditStageModal = false">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Etapa</h3>
                <form @submit.prevent="updateStage">
                    <TextInput v-model="stageForm.name" type="text" class="w-full" placeholder="Nombre de la etapa" required autofocus />
                    <div class="mt-4 flex justify-end gap-2">
                        <SecondaryButton @click="showEditStageModal = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="stageForm.processing">Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Task Modal -->
        <Modal :show="showAddTaskModal" @close="showAddTaskModal = false">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Nueva Tarea</h3>
                <form @submit.prevent="addTask" class="space-y-3">
                    <TextInput v-model="taskForm.name" type="text" class="w-full" placeholder="Nombre de la tarea" required autofocus />
                    <textarea v-model="taskForm.description" rows="2" class="w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md text-sm" placeholder="Descripción (opcional)"></textarea>
                    <div>
                        <label class="text-xs text-gray-500">Prioridad</label>
                        <select v-model="taskForm.priority" class="w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md text-sm mt-1">
                            <option value="low">Baja</option>
                            <option value="medium">Media</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" v-model="taskForm.has_subtasks" id="has_subtasks_new" 
                            class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded" />
                        <label for="has_subtasks_new" class="text-sm text-gray-700">Esta tarea tendrá subtareas</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <SecondaryButton @click="showAddTaskModal = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="taskForm.processing">Crear</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Task Modal -->
        <Modal :show="showEditTaskModal" @close="showEditTaskModal = false">
            <div class="p-5">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Tarea</h3>
                <form @submit.prevent="updateTask" class="space-y-3">
                    <TextInput v-model="taskForm.name" type="text" class="w-full" placeholder="Nombre de la tarea" required autofocus />
                    <textarea v-model="taskForm.description" rows="2" class="w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md text-sm" placeholder="Descripción (opcional)"></textarea>
                    <div>
                        <label class="text-xs text-gray-500">Prioridad</label>
                        <select v-model="taskForm.priority" class="w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md text-sm mt-1">
                            <option value="low">Baja</option>
                            <option value="medium">Media</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 py-1">
                        <input type="checkbox" v-model="taskForm.has_subtasks" id="has_subtasks_edit" 
                            class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded" />
                        <label for="has_subtasks_edit" class="text-sm text-gray-700">Esta tarea tendrá subtareas</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <SecondaryButton @click="showEditTaskModal = false" type="button">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="taskForm.processing">Guardar</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>