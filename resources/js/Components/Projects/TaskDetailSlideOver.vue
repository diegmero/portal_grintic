<script setup>
import { ref, watch, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    CalendarIcon,
    CheckCircleIcon as CheckCircleIconOutline,
    TrashIcon,
    PencilSquareIcon,
    PlusIcon,
    FlagIcon,
    ChevronUpDownIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';
import CommentsSection from '@/Components/Comments/CommentsSection.vue';
import Dropdown from '@/Components/Dropdown.vue';

const props = defineProps({
    show: Boolean,
    task: Object,         // null = create mode
    stageId: String,      // required for create mode
});

const emit = defineEmits(['close', 'created']);

// Create mode detection
const isCreateMode = computed(() => props.show && !props.task && props.stageId);

// Local form for editing/creating
const form = useForm({
    name: '',
    description: '',
    priority: 'medium',
    due_date: '',
    has_subtasks: true,
});

// Priority config
const priorityConfig = {
    'low': { label: 'Baja', class: 'bg-gray-100 text-gray-600', flagClass: 'text-gray-400' },
    'medium': { label: 'Media', class: 'bg-blue-100 text-blue-700', flagClass: 'text-blue-500' },
    'high': { label: 'Alta', class: 'bg-orange-100 text-orange-700', flagClass: 'text-orange-500' },
    'urgent': { label: 'Urgente', class: 'bg-red-100 text-red-700', flagClass: 'text-red-500' },
};

// Status config
const statusConfig = {
    'pending': { label: 'Pendiente', class: 'bg-gray-100 text-gray-700' },
    'in_progress': { label: 'En Progreso', class: 'bg-blue-100 text-blue-700' },
    'completed': { label: 'Completada', class: 'bg-green-100 text-green-700' },
};

// Date formatting
const formatDate = (dateString) => {
    if (!dateString) return null;
    return new Date(dateString).toLocaleDateString('es-ES', { 
        day: '2-digit', 
        month: 'short',
        timeZone: 'UTC'
    });
};

const formatDateInput = (dateString) => {
    if (!dateString) return '';
    // Take first 10 chars (YYYY-MM-DD) to match input type="date"
    return dateString.substring(0, 10);
};

// Subtasks
const newSubtaskName = ref('');
const editingSubtask = ref(null);
const editSubtaskName = ref('');

const subtaskProgress = computed(() => {
    if (!props.task?.subtasks?.length) return 0;
    const completed = props.task.subtasks.filter(s => s.is_completed).length;
    return Math.round((completed / props.task.subtasks.length) * 100);
});

// Deferred Watcher Logic
watch(() => props.show, (showing) => {
    if (showing) {
        // Deferred Load
        setTimeout(() => {
            if (props.task) {
                // Edit mode
                form.name = props.task.name || '';
                form.description = props.task.description || '';
                form.priority = props.task.priority || 'medium';
                form.due_date = formatDateInput(props.task.due_date);
                form.has_subtasks = props.task.has_subtasks !== false;
            } else {
                // Create mode
                form.reset();
                form.priority = 'medium';
                form.has_subtasks = true;
            }
        }, 100);
    } else {
        // Deferred clear handled implicitly or we can force reset if needed
        // setTimeout(() => form.reset(), 500); 
        // Keeping it simple as parent might handle task prop clearing
    }
});

// Watch task prop strictly for updates while open
watch(() => props.task, (newTask) => {
    if (props.show && newTask) {
        // Update form if we receive new data while open (e.g. after save)
        form.name = newTask.name || '';
        form.description = newTask.description || '';
        form.priority = newTask.priority || 'medium';
        form.due_date = formatDateInput(newTask.due_date);
        form.has_subtasks = newTask.has_subtasks !== false;
    }
});


// Actions
const saveField = (field) => {
    if (!props.task) return;
    router.patch(route('tasks.update', props.task.id), {
        [field]: form[field],
    }, { preserveScroll: true });
};

const createTask = () => {
    if (!form.name.trim() || !props.stageId) return;
    
    router.post(route('tasks.store', props.stageId), {
        name: form.name,
        description: form.description,
        priority: form.priority,
        due_date: form.due_date || null,
        has_subtasks: form.has_subtasks,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('created');
            emit('close');
        }
    });
};

const toggleTaskStatus = () => {
    if (!props.task) return;
    router.patch(route('tasks.update', props.task.id), {
        is_completed: props.task.status !== 'completed',
    }, { preserveScroll: true });
};

const addSubtask = () => {
    if (!newSubtaskName.value.trim() || !props.task) return;
    router.post(route('subtasks.store', props.task.id), {
        name: newSubtaskName.value,
    }, { 
        preserveScroll: true,
        onSuccess: () => { newSubtaskName.value = ''; }
    });
};

const toggleSubtask = (subtask) => {
    router.patch(route('subtasks.update', subtask.id), {
        is_completed: !subtask.is_completed,
    }, { preserveScroll: true });
};

const startEditSubtask = (subtask) => {
    editingSubtask.value = subtask.id;
    editSubtaskName.value = subtask.name;
};

const saveSubtaskEdit = (subtaskId) => {
    if (editSubtaskName.value.trim()) {
        router.patch(route('subtasks.update', subtaskId), {
            name: editSubtaskName.value,
        }, { preserveScroll: true });
    }
    editingSubtask.value = null;
};

const deleteSubtask = (subtaskId) => {
    if (confirm('¿Eliminar esta subtarea?')) {
        router.delete(route('subtasks.destroy', subtaskId), { preserveScroll: true });
    }
};

const deleteTask = () => {
    if (confirm('¿Eliminar esta tarea y todas sus subtareas?')) {
        router.delete(route('tasks.destroy', props.task.id), { 
            preserveScroll: true,
            onSuccess: () => emit('close')
        });
    }
};
</script>

<template>
    <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <Transition
            enter-active-class="ease-in-out duration-500"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in-out duration-500"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('close')"></div>
        </Transition>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <Transition
                        enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div v-show="show" class="pointer-events-auto w-screen max-w-lg">
                            <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                
                                <!-- Header -->
                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
                                    <div class="flex items-center gap-3">
                                        <!-- Create mode: show icon -->
                                        <template v-if="isCreateMode">
                                            <PlusIcon class="h-6 w-6 text-brand" />
                                            <span class="text-base font-semibold text-gray-700">Nueva Tarea</span>
                                        </template>
                                        <!-- Edit mode: show completion toggle -->
                                        <template v-else>
                                            <button @click="toggleTaskStatus" class="flex-shrink-0 transition-transform hover:scale-110">
                                                <CheckCircleIconSolid v-if="task?.status === 'completed'" class="h-7 w-7 text-green-500" />
                                                <CheckCircleIconOutline v-else class="h-7 w-7 text-gray-300 hover:text-green-400" />
                                            </button>
                                            <div>
                                                <span :class="[statusConfig[task?.status]?.class || 'bg-gray-100 text-gray-700', 'text-xs font-medium px-2 py-0.5 rounded-full']">
                                                    {{ statusConfig[task?.status]?.label || task?.status }}
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button v-if="!isCreateMode" @click="deleteTask" class="p-2 text-gray-400 hover:text-red-500 rounded-lg hover:bg-gray-100 transition-colors">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                        <button @click="$emit('close')" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-colors">
                                            <XMarkIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 overflow-y-auto">
                                    <div class="p-5 space-y-6">
                                        
                                        <!-- Task Name -->
                                        <div>
                                            <input 
                                                v-model="form.name" 
                                                @blur="saveField('name')"
                                                @keyup.enter="saveField('name')"
                                                type="text"
                                                class="w-full text-xl font-semibold text-gray-900 border-0 p-0 focus:ring-0 placeholder-gray-400"
                                                placeholder="Nombre de la tarea"
                                            />
                                        </div>

                                        <!-- Quick Fields Row -->
                                        <div class="flex flex-wrap gap-3">
                                            <!-- Due Date -->
                                            <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">
                                                <CalendarIcon class="h-4 w-4 text-gray-400" />
                                                <input 
                                                    type="date"
                                                    v-model="form.due_date"
                                                    @change="saveField('due_date')"
                                                    class="text-sm border-0 bg-transparent p-0 focus:ring-0 text-gray-700"
                                                />
                                            </div>

                                            <!-- Priority -->
                                            <Dropdown align="left" width="48">
                                                <template #trigger>
                                                    <button class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100 hover:bg-gray-100 transition-colors w-full sm:w-auto justify-between sm:justify-start group">
                                                        <FlagIcon :class="['h-4 w-4', priorityConfig[form.priority]?.flagClass || 'text-gray-400']" />
                                                        <span class="text-sm text-gray-700 font-medium">{{ priorityConfig[form.priority]?.label || 'Prioridad' }}</span>
                                                        <ChevronUpDownIcon class="h-4 w-4 text-gray-400 group-hover:text-gray-600" />
                                                    </button>
                                                </template>
                                                <template #content>
                                                    <div class="py-1">
                                                        <button 
                                                            v-for="(config, key) in priorityConfig" 
                                                            :key="key"
                                                            @click="form.priority = key; saveField('priority')"
                                                            class="w-full text-left px-4 py-2.5 text-sm flex items-center gap-3 hover:bg-gray-50 transition-colors"
                                                        >
                                                            <FlagIcon :class="['h-4 w-4', config.flagClass]" />
                                                            <span class="font-medium text-gray-700">{{ config.label }}</span>
                                                        </button>
                                                    </div>
                                                </template>
                                            </Dropdown>

                                            <!-- Has Subtasks Toggle -->
                                            <label class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100 cursor-pointer hover:bg-gray-100 transition-colors">
                                                <input 
                                                    type="checkbox" 
                                                    v-model="form.has_subtasks"
                                                    @change="saveField('has_subtasks')"
                                                    class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded"
                                                />
                                                <span class="text-sm text-gray-700">Con subtareas</span>
                                            </label>
                                        </div>

                                        <!-- Description -->
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Descripción</label>
                                            <textarea 
                                                v-model="form.description"
                                                @blur="saveField('description')"
                                                rows="3"
                                                class="w-full text-sm text-gray-700 border border-gray-200 rounded-lg p-3 focus:border-brand focus:ring-1 focus:ring-brand resize-none"
                                                placeholder="Agrega una descripción..."
                                            ></textarea>
                                        </div>

                                        <!-- Subtasks Section -->
                                        <div v-if="form.has_subtasks" class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Subtareas</label>
                                                <span class="text-xs text-gray-400">{{ subtaskProgress }}% completado</span>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                                <div class="bg-brand h-1.5 rounded-full transition-all duration-300" :style="{ width: subtaskProgress + '%' }"></div>
                                            </div>

                                            <!-- Subtask List -->
                                            <div class="space-y-1">
                                                <div 
                                                    v-for="subtask in (task?.subtasks || [])" 
                                                    :key="subtask.id" 
                                                    class="flex items-center gap-2 py-2 px-2 rounded-lg hover:bg-gray-50 group transition-colors"
                                                >
                                                    <button @click="toggleSubtask(subtask)" class="flex-shrink-0">
                                                        <CheckCircleIconSolid v-if="subtask.is_completed" class="h-5 w-5 text-green-500" />
                                                        <CheckCircleIconOutline v-else class="h-5 w-5 text-gray-300 hover:text-green-400" />
                                                    </button>
                                                    
                                                    <template v-if="editingSubtask === subtask.id">
                                                        <input 
                                                            v-model="editSubtaskName" 
                                                            @keyup.enter="saveSubtaskEdit(subtask.id)" 
                                                            @blur="saveSubtaskEdit(subtask.id)"
                                                            class="flex-1 text-sm border border-brand rounded px-2 py-1 focus:ring-1 focus:ring-brand"
                                                            autofocus
                                                        />
                                                    </template>
                                                    <template v-else>
                                                        <span :class="['text-sm flex-1 cursor-pointer select-none', subtask.is_completed ? 'text-gray-400 line-through' : 'text-gray-700']" @click="startEditSubtask(subtask)">
                                                            {{ subtask.name }}
                                                        </span>
                                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <button @click="startEditSubtask(subtask)" class="p-1 text-gray-400 hover:text-brand rounded">
                                                                <PencilSquareIcon class="h-4 w-4" />
                                                            </button>
                                                            <button @click="deleteSubtask(subtask.id)" class="p-1 text-gray-400 hover:text-red-500 rounded">
                                                                <TrashIcon class="h-4 w-4" />
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Add Subtask -->
                                                <div class="flex items-center gap-2 py-2 px-2">
                                                    <PlusIcon class="h-5 w-5 text-gray-300" />
                                                    <input 
                                                        v-model="newSubtaskName"
                                                        @keyup.enter="addSubtask"
                                                        type="text"
                                                        placeholder="Agregar subtarea..."
                                                        class="flex-1 text-sm border-0 bg-transparent p-0 focus:ring-0 placeholder-gray-400"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Comments Section (only in edit mode) -->
                                        <div v-if="!isCreateMode" class="space-y-3">
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Comentarios</label>
                                            <CommentsSection 
                                                :commentable-id="task?.id" 
                                                commentable-type="App\Models\Task"
                                                :initial-comments="task?.comments || []" 
                                            />
                                        </div>

                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                                    <!-- Create mode: show Create button -->
                                    <template v-if="isCreateMode">
                                        <button 
                                            @click="createTask"
                                            :disabled="!form.name.trim()"
                                            class="w-full py-2.5 px-4 bg-brand text-white rounded-lg font-medium hover:bg-brand/90 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                                        >
                                            Crear Tarea
                                        </button>
                                    </template>
                                    <!-- Edit mode: show metadata -->
                                    <template v-else-if="task">
                                        <div class="text-xs text-gray-400 flex items-center justify-between">
                                            <span>Creada: {{ formatDate(task.created_at) || '—' }}</span>
                                            <span v-if="task.due_date" :class="[new Date(task.due_date) < new Date() ? 'text-red-500 font-medium' : '']">
                                                Vence: {{ formatDate(task.due_date) }}
                                            </span>
                                        </div>
                                    </template>
                                </div>

                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
