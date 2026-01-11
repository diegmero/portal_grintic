<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    PlusIcon, 
    TrashIcon, 
    ChatBubbleLeftIcon,
    PaperClipIcon,
    CheckCircleIcon,
    ClockIcon,
    ArrowLeftIcon
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    project: Object,
});

// Priority colors
const priorityColors = {
    'low': 'bg-gray-100 text-gray-600',
    'medium': 'bg-blue-100 text-blue-700',
    'high': 'bg-yellow-100 text-yellow-700',
    'urgent': 'bg-red-100 text-red-700',
};

const priorityLabels = {
    'low': 'Baja',
    'medium': 'Media',
    'high': 'Alta',
    'urgent': 'Urgente',
};

// Stage Modal
const showAddStageModal = ref(false);
const stageForm = useForm({ name: '' });

const addStage = () => {
    stageForm.post(route('stages.store', props.project.id), {
        onSuccess: () => {
            stageForm.reset();
            showAddStageModal.value = false;
        },
        preserveScroll: true,
    });
};

const deleteStage = (stageId) => {
    if (confirm('¿Eliminar esta etapa y todas sus tareas?')) {
        router.delete(route('stages.destroy', stageId), { preserveScroll: true });
    }
};

// Task Modal
const showAddTaskModal = ref(false);
const activeStageIdForTask = ref(null);
const taskForm = useForm({ name: '', description: '', weight: 1, priority: 'medium' });

const openAddTaskModal = (stageId) => {
    activeStageIdForTask.value = stageId;
    taskForm.reset();
    taskForm.priority = 'medium';
    showAddTaskModal.value = true;
};

const addTask = () => {
    taskForm.post(route('tasks.store', activeStageIdForTask.value), {
        onSuccess: () => {
            taskForm.reset();
            showAddTaskModal.value = false;
        },
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

// Calculate stage progress
const getStageProgress = (stage) => {
    if (!stage.tasks || stage.tasks.length === 0) return 0;
    const completed = stage.tasks.filter(t => t.status === 'completed').length;
    return Math.round((completed / stage.tasks.length) * 100);
};
</script>

<template>
    <Head :title="`Board: ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('projects.show', project.id)" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold leading-tight text-gray-900">{{ project.name }}</h2>
                        <p class="text-sm text-gray-500">Vista de Tablero</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <span class="text-sm text-gray-500">Progreso</span>
                        <span class="ml-2 text-lg font-bold text-gray-900">{{ project.progress }}%</span>
                    </div>
                    <PrimaryButton @click="showAddStageModal = true">
                        <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                        Nueva Etapa
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-6 h-[calc(100vh-160px)] overflow-hidden">
            <div class="mx-auto max-w-full px-4 h-full">
                
                <!-- Kanban Board -->
                <div class="flex gap-6 h-full overflow-x-auto pb-4">
                    
                    <!-- Stage Columns -->
                    <div v-for="stage in project.stages" :key="stage.id" class="flex-shrink-0 w-80 bg-gray-50 rounded-xl flex flex-col max-h-full">
                        
                        <!-- Column Header -->
                        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-6 bg-brand rounded-full"></span>
                                <h3 class="font-bold text-gray-900">{{ stage.name }}</h3>
                                <span class="text-xs text-gray-400 bg-white px-2 py-0.5 rounded-full">{{ stage.tasks.length }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="openAddTaskModal(stage.id)" class="p-1.5 text-gray-400 hover:text-brand transition-colors rounded-md hover:bg-white">
                                    <PlusIcon class="h-4 w-4" />
                                </button>
                                <button @click="deleteStage(stage.id)" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors rounded-md hover:bg-white">
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="px-4 py-2">
                            <div class="w-full bg-gray-200 rounded-full h-1">
                                <div class="bg-brand h-1 rounded-full transition-all" :style="{ width: getStageProgress(stage) + '%' }"></div>
                            </div>
                        </div>

                        <!-- Task Cards -->
                        <div class="flex-1 overflow-y-auto p-3 space-y-3">
                            <div v-for="task in stage.tasks" :key="task.id" 
                                class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 hover:shadow-md transition-shadow cursor-pointer group">
                                
                                <!-- Task Header -->
                                <div class="flex items-start gap-2">
                                    <button @click="toggleTask(task)" class="mt-0.5 flex-shrink-0">
                                        <CheckCircleIconSolid v-if="task.status === 'completed'" class="h-5 w-5 text-green-500" />
                                        <CheckCircleIcon v-else class="h-5 w-5 text-gray-300 hover:text-green-400" />
                                    </button>
                                    <div class="flex-1 min-w-0">
                                        <h4 :class="['text-sm font-medium truncate', task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900']">
                                            {{ task.name }}
                                        </h4>
                                    </div>
                                    <button @click="deleteTask(task.id)" class="opacity-0 group-hover:opacity-100 p-1 text-gray-400 hover:text-red-500 transition-opacity">
                                        <TrashIcon class="h-3.5 w-3.5" />
                                    </button>
                                </div>

                                <!-- Description -->
                                <p v-if="task.description" class="text-xs text-gray-500 mt-1 line-clamp-2 ml-7">{{ task.description }}</p>

                                <!-- Footer -->
                                <div class="flex items-center justify-between mt-3 ml-7">
                                    <div class="flex items-center gap-2">
                                        <span :class="[priorityColors[task.priority] || 'bg-gray-100 text-gray-600', 'text-[10px] font-medium px-1.5 py-0.5 rounded']">
                                            {{ priorityLabels[task.priority] || task.priority }}
                                        </span>
                                        <span class="text-[10px] text-gray-400 font-mono">w:{{ task.weight }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-400">
                                        <div v-if="task.subtasks?.length" class="flex items-center gap-0.5 text-[10px]">
                                            <CheckCircleIcon class="h-3 w-3" />
                                            {{ task.subtasks.filter(s => s.is_completed).length }}/{{ task.subtasks.length }}
                                        </div>
                                        <div v-if="task.comments?.length" class="flex items-center gap-0.5 text-[10px]">
                                            <ChatBubbleLeftIcon class="h-3 w-3" />
                                            {{ task.comments.length }}
                                        </div>
                                        <div v-if="task.media?.length" class="flex items-center gap-0.5 text-[10px]">
                                            <PaperClipIcon class="h-3 w-3" />
                                            {{ task.media.length }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-if="stage.tasks.length === 0" class="text-center py-8">
                                <p class="text-xs text-gray-400">Sin tareas</p>
                                <button @click="openAddTaskModal(stage.id)" class="mt-2 text-xs text-brand hover:underline">
                                    + Crear tarea
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Add Stage Column -->
                    <div class="flex-shrink-0 w-80">
                        <button @click="showAddStageModal = true" 
                            class="w-full h-32 border-2 border-dashed border-gray-300 rounded-xl flex flex-col items-center justify-center text-gray-400 hover:border-brand hover:text-brand transition-colors">
                            <PlusIcon class="h-8 w-8 mb-1" />
                            <span class="text-sm font-medium">Nueva Etapa</span>
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <!-- Add Stage Modal -->
        <Modal :show="showAddStageModal" @close="showAddStageModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Nueva Etapa</h3>
                <form @submit.prevent="addStage">
                    <div>
                        <label for="stage-name" class="block text-sm font-medium text-gray-700">Nombre de la Etapa</label>
                        <TextInput id="stage-name" v-model="stageForm.name" type="text" class="mt-1 block w-full" required autofocus />
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="showAddStageModal = false">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="stageForm.processing">Crear Etapa</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Add Task Modal -->
        <Modal :show="showAddTaskModal" @close="showAddTaskModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Nueva Tarea</h3>
                <form @submit.prevent="addTask" class="space-y-4">
                    <div>
                        <label for="task-name" class="block text-sm font-medium text-gray-700">Nombre de la Tarea</label>
                        <TextInput id="task-name" v-model="taskForm.name" type="text" class="mt-1 block w-full" required autofocus />
                    </div>
                    <div>
                        <label for="task-desc" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                        <textarea id="task-desc" v-model="taskForm.description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="task-priority" class="block text-sm font-medium text-gray-700">Prioridad</label>
                            <CustomSelect
                                v-model="taskForm.priority"
                                :options="[
                                    { value: 'low', label: 'Baja' },
                                    { value: 'medium', label: 'Media' },
                                    { value: 'high', label: 'Alta' },
                                    { value: 'urgent', label: 'Urgente' },
                                ]"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <label for="task-weight" class="block text-sm font-medium text-gray-700">Peso</label>
                            <TextInput id="task-weight" v-model="taskForm.weight" type="number" step="0.1" min="0.1" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <SecondaryButton @click="showAddTaskModal = false">Cancelar</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="taskForm.processing">Crear Tarea</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
