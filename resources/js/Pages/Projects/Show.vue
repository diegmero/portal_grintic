<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
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
import TaskDetailSlideOver from '@/Components/Projects/TaskDetailSlideOver.vue';
import ProjectEditSlideOver from '@/Components/Projects/ProjectEditSlideOver.vue';

const showEditProject = ref(false);
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
// File Upload for Stage
const uploadFileToStage = (stageId, event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Client-side validation
    if (file.type !== 'application/pdf') {
        alert('Solo se permiten archivos PDF.');
        event.target.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) { // 5MB
        alert('El archivo no puede superar los 5MB.');
        event.target.value = '';
        return;
    }
    
    router.post(route('stages.media.store', stageId), { file }, {
        forceFormData: true,
        preserveScroll: true,
        onError: (errors) => {
            if (errors.file) alert(errors.file);
        }
    });
    event.target.value = '';
};

// File Preview
const showFilePreviewModal = ref(false);
const previewFileUrl = ref('');
const previewFileName = ref('');

const openFilePreview = (url, fileName) => {
    previewFileUrl.value = url;
    previewFileName.value = fileName;
    showFilePreviewModal.value = true;
};

const closeFilePreview = () => {
    showFilePreviewModal.value = false;
    previewFileUrl.value = '';
    previewFileName.value = '';
};

const deleteStageFile = (stageId, mediaId) => {
    if (confirm('¿Eliminar este archivo?')) {
        router.delete(route('stages.media.destroy', { stage: stageId, media: mediaId }), { preserveScroll: true });
    }
};

// --- Task Management ---
const activeStageIdForTask = ref(null);

// Slide-over for task details (edit and create modes)
const showTaskSlideOver = ref(false);
const selectedTask = ref(null);

// Open slide-over in create mode
const openCreateTaskSlideOver = (stageId) => {
    selectedTask.value = null;
    activeStageIdForTask.value = stageId;
    showTaskSlideOver.value = true;
};

// Open slide-over in edit mode
const openTaskSlideOver = (task) => {
    selectedTask.value = task;
    activeStageIdForTask.value = null;
    showTaskSlideOver.value = true;
};

// Sync selectedTask when props change (real-time updates)
watch(() => props.project, (newProject) => {
    if (selectedTask.value && showTaskSlideOver.value) {
        // Find the updated task in the new project data
        for (const stage of newProject.stages) {
            const foundTask = stage.tasks.find(t => t.id === selectedTask.value.id);
            if (foundTask) {
                selectedTask.value = foundTask;
                break;
            }
        }
    }
}, { deep: true });

const closeTaskSlideOver = () => {
    showTaskSlideOver.value = false;
    selectedTask.value = null;
    activeStageIdForTask.value = null;
};

// Priority config
const priorityConfig = {
    'low': { label: 'Baja', class: 'bg-gray-100 text-gray-600' },
    'medium': { label: 'Media', class: 'bg-blue-100 text-blue-700' },
    'high': { label: 'Alta', class: 'bg-orange-100 text-orange-700' },
    'urgent': { label: 'Urgente', class: 'bg-red-100 text-red-700' },
};

const toggleTask = (task, event) => {
    event.stopPropagation();
    router.patch(route('tasks.update', task.id), {
        is_completed: task.status !== 'completed',
    }, { preserveScroll: true });
};

// Date formatting
const formatShortDate = (dateString) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: 'short',
        timeZone: 'UTC'
    });
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
                <button @click="showEditProject = true" class="text-gray-400 hover:text-brand transition-colors p-1 rounded-full hover:bg-gray-100" title="Editar Proyecto">
                    <PencilSquareIcon class="h-4 w-4" />
                </button>
            </div>
        </template>

        <div class="py-0">

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    
                    <!-- Sidebar - Project Info (Compact) -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg border border-gray-200 sticky top-4">
                            <!-- Status Header -->
                            <div class="p-3 border-b border-gray-100">
                                <div class="flex items-center gap-2 mb-2">
                                    <span :class="[statusConfig[project.status]?.class || 'bg-gray-500', 'w-2 h-2 rounded-full']"></span>
                                    <span class="text-sm font-medium text-gray-700">{{ statusConfig[project.status]?.label || project.status }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
                                    <span>Progreso</span>
                                    <span class="font-semibold text-gray-900">{{ project.progress }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-brand h-1.5 rounded-full" :style="{ width: project.progress + '%' }"></div>
                                </div>
                            </div>

                            <!-- Info List -->
                            <div class="p-3 space-y-2 text-sm">
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

                            <!-- Project Files (Secure) -->
                            <div class="p-3 border-t border-gray-100">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-semibold text-gray-900">Archivos del Proyecto</h4>
                                </div>
                                <div v-if="project.media && project.media.length > 0" class="space-y-1">
                                    <button 
                                        v-for="file in project.media" 
                                        :key="file.id"
                                        @click="openFilePreview(route('projects.media.show', { project: project.id, media: file.id }), file.file_name)"
                                        class="flex items-center gap-2 text-sm text-gray-600 hover:text-brand w-full text-left truncate group px-1 py-0.5 rounded hover:bg-gray-50"
                                    >
                                        <PaperClipIcon class="h-4 w-4 flex-shrink-0 text-gray-400 group-hover:text-brand" />
                                        <span class="truncate">{{ file.file_name }}</span>
                                    </button>
                                </div>
                                <p v-else class="text-sm text-gray-400 px-1">Sin archivos adjuntos.</p>
                            </div>

                            <!-- Description -->
                            <div v-if="project.description" class="p-3 border-t border-gray-100">
                                <p class="text-sm text-gray-700 font-semibold tracking-wide mb-1">Descripción</p>
                                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ project.description }}</p>
                            </div>

                             <!-- Edit Button -->
                             <div class="p-3 border-t border-gray-100">
                                <button @click="showEditProject = true" class="w-full flex justify-center items-center gap-2 rounded-md bg-white border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                    <PencilSquareIcon class="h-4 w-4 text-gray-500" />
                                    Editar Proyecto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="lg:col-span-4 min-w-0">
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
                                                <h3 class="font-semibold text-gray-900 text-base">{{ stage.name }}</h3>
                                                <span class="text-sm text-gray-700">{{ stage.tasks.filter(t => t.status === 'completed').length }}/{{ stage.tasks.length }} tareas</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <!-- Stage Progress -->
                                            <div class="flex items-center gap-2">
                                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-brand h-2 rounded-full transition-all" 
                                                        :style="{ width: getStageProgress(stage) + '%' }"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-600 w-8">
                                                    {{ getStageProgress(stage) }}%
                                                </span>
                                            </div>
                                            <!-- Actions -->
                                            <div class="flex items-center gap-1">
                                                <button @click="openCreateTaskSlideOver(stage.id)" class="p-1.5 text-gray-400 hover:text-brand rounded" title="Agregar Tarea">
                                                    <PlusIcon class="h-5 w-5" />
                                                </button>
                                                <label class="p-1.5 text-gray-400 hover:text-brand rounded cursor-pointer" title="Subir Archivo">
                                                    <ArrowUpTrayIcon class="h-5 w-5" />
                                                    <input type="file" class="hidden" @change="uploadFileToStage(stage.id, $event)" />
                                                </label>
                                                <button @click="openEditStageModal(stage)" class="p-1.5 text-gray-400 hover:text-brand rounded" title="Editar Etapa">
                                                    <PencilSquareIcon class="h-5 w-5" />
                                                </button>
                                                <button @click="deleteStage(stage.id)" class="p-1.5 text-gray-400 hover:text-red-500 rounded" title="Eliminar">
                                                    <TrashIcon class="h-5 w-5" />
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
                                                <button @click="openFilePreview(route('stages.media.show', { stage: stage.id, media: file.id }), file.file_name)" class="text-brand hover:underline max-w-[150px] truncate text-left">
                                                    {{ file.file_name }}
                                                </button>
                                            <button @click="deleteStageFile(stage.id, file.id)" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100">
                                                <XMarkIcon class="h-3 w-3" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tasks List -->
                                <div class="divide-y divide-gray-50">
                                    <div 
                                        v-for="task in stage.tasks" 
                                        :key="task.id" 
                                        @click="openTaskSlideOver(task)"
                                        class="px-4 py-2.5 hover:bg-gray-50 flex items-center gap-3 cursor-pointer group transition-colors"
                                    >
                                        <!-- Checkbox -->
                                        <button @click="toggleTask(task, $event)" class="flex-shrink-0 transition-transform hover:scale-110">
                                            <CheckCircleIconSolid v-if="task.status === 'completed'" class="h-5 w-5 text-green-500" />
                                            <CheckCircleIconOutline v-else class="h-5 w-5 text-gray-300 hover:text-green-400" />
                                        </button>
                                        
                                        <!-- Task Name & Subtask Badge -->
                                        <div class="flex-1 min-w-0">
                                            <span :class="['text-sm font-medium', task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900']">
                                                {{ task.name }}
                                            </span>
                                            <span v-if="task.has_subtasks && task.subtasks?.length" class="ml-2 text-[10px] text-gray-400">
                                                <span class="inline-flex items-center gap-0.5">
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                    </svg>
                                                    {{ task.subtasks.filter(s => s.is_completed).length }}/{{ task.subtasks.length }}
                                                </span>
                                            </span>
                                        </div>

                                        <!-- Due Date -->
                                        <span v-if="task.due_date" :class="[
                                            'text-xs px-1.5 py-0.5 rounded',
                                            new Date(task.due_date) < new Date() ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500'
                                        ]">
                                            {{ formatShortDate(task.due_date) }}
                                        </span>

                                        <!-- Priority Badge -->
                                        <span :class="[priorityConfig[task.priority]?.class || 'bg-gray-100 text-gray-600', 'text-xs font-medium px-1.5 py-0.5 rounded']">
                                            {{ priorityConfig[task.priority]?.label || task.priority }}
                                        </span>

                                        <!-- Arrow indicator -->
                                        <ChevronRightIcon class="h-4 w-4 text-gray-300 opacity-0 group-hover:opacity-100 transition-opacity" />
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

        <!-- File Preview Modal -->
        <Modal :show="showFilePreviewModal" @close="closeFilePreview" maxWidth="3xl">
            <div class="p-4 h-[80vh] flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ previewFileName }}</h3>
                    <button @click="closeFilePreview" class="text-gray-400 hover:text-gray-500">
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>
                <div class="flex-1 bg-gray-100 rounded-lg overflow-hidden relative">
                    <iframe 
                        v-if="previewFileUrl" 
                        :src="previewFileUrl" 
                        class="w-full h-full border-0"
                    ></iframe>
                    <div v-else class="absolute inset-0 flex items-center justify-center text-gray-400">
                        Cargando...
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Task Detail Slide-Over (Asana/ClickUp style - for create and edit) -->
        <TaskDetailSlideOver 
            :show="showTaskSlideOver" 
            :task="selectedTask" 
            :stage-id="activeStageIdForTask"
            @close="closeTaskSlideOver" 
        />

        <ProjectEditSlideOver 
            :show="showEditProject" 
            :project="project" 
            @close="showEditProject = false"
            @preview-file="(file) => openFilePreview(route('projects.media.show', { project: project.id, media: file.id }), file.file_name)"
        />

    </AuthenticatedLayout>
</template>