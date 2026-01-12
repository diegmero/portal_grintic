<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TaskDetailSlideOver from '@/Components/Projects/TaskDetailSlideOver.vue';
import ProjectEditSlideOver from '@/Components/Projects/ProjectEditSlideOver.vue';
import { 
    CalendarIcon, 
    DocumentTextIcon, 
    PaperClipIcon, 
    ChatBubbleLeftIcon,
    CurrencyDollarIcon,
    ChartBarIcon,
    CheckCircleIcon,
    XMarkIcon,
    EyeIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    ArrowUpTrayIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/vue/24/outline';
import { FlagIcon } from '@heroicons/vue/24/solid';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    project: Object,
    files: Array,
});

// Ensure route is available
const route = window.route;

// Calculate total progress
const projectProgress = computed(() => {
    if (!props.project.stages || props.project.stages.length === 0) return 0;
    
    let totalTasks = 0;
    let completedTasks = 0;
    
    props.project.stages.forEach(stage => {
        if (stage.tasks) {
            totalTasks += stage.tasks.length;
            completedTasks += stage.tasks.filter(t => t.status === 'completed').length;
        }
    });
    
    return totalTasks === 0 ? 0 : Math.round((completedTasks / totalTasks) * 100);
});

// Format dates
const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-ES', { 
        year: 'numeric', month: 'short', day: 'numeric' 
    });
};

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-CO', { 
        style: 'currency', currency: 'COP' 
    }).format(amount);
};

// Priority Helpers
const priorityConfig = {
    'low': { label: 'Baja', color: 'text-gray-400', bg: 'bg-gray-100' },
    'medium': { label: 'Media', color: 'text-blue-500', bg: 'bg-blue-100' },
    'high': { label: 'Alta', color: 'text-orange-500', bg: 'bg-orange-100' },
    'urgent': { label: 'Urgente', color: 'text-red-500', bg: 'bg-red-100' },
};

// Status config
const statusConfig = {
    'active': { label: 'Activo', class: 'bg-green-500', text: 'text-green-700', bg_light: 'bg-green-50' },
    'on_hold': { label: 'En Pausa', class: 'bg-yellow-500', text: 'text-yellow-700', bg_light: 'bg-yellow-50' },
    'completed': { label: 'Completado', class: 'bg-brand', text: 'text-brand', bg_light: 'bg-brand/10' },
    'in_progress': { label: 'En Progreso', class: 'bg-blue-500', text: 'text-blue-700', bg_light: 'bg-blue-50' },
    'pending': { label: 'Pendiente', class: 'bg-gray-400', text: 'text-gray-700', bg_light: 'bg-gray-50' },
};

// SlideOver Control
const selectedTask = ref(null);
const showSlideOver = ref(false);
const activeStageIdForTask = ref(null);

const closeSlideOver = () => {
    showSlideOver.value = false;
    setTimeout(() => { selectedTask.value = null; activeStageIdForTask.value = null; }, 300);
};

// File Preview Logic
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

// Files Organizing
const projectFiles = computed(() => props.files || []);

// Permission Helpers
const page = usePage();
const can = (permission) => page.props.auth.user?.permissions?.some(p => p.name === permission) || false;

// --- Project Edit ---
const showEditProject = ref(false);

// --- Stage Management ---
const showAddStageModal = ref(false);
const stageForm = useForm({ name: '' });

const addStage = () => {
    stageForm.post(route('stages.store', props.project.id), {
        onSuccess: () => { stageForm.reset(); showAddStageModal.value = false; },
        preserveScroll: true,
    });
};

// --- Task Management ---
// Open slide-over in create mode
const openCreateTaskSlideOver = (stageId) => {
    selectedTask.value = null;
    activeStageIdForTask.value = stageId;
    showSlideOver.value = true;
};

// Open existing task
const openTask = (task) => {
    selectedTask.value = task;
    activeStageIdForTask.value = task.stage_id;
    showSlideOver.value = true;
};

// --- File Upload ---
const uploadFileToStage = (stageId, event) => {
    const file = event.target.files[0];
    if (!file) return;

    // Basic Validation
    if (file.size > 10 * 1024 * 1024) { // 10MB
        alert('El archivo no puede superar los 10MB.');
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

</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
                <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                        {{ project.name }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 flex items-center gap-2">
                        <span>{{ project.company.name }}</span>
                    </p>
                </div>
                <div v-if="can('edit_project')">
                     <button 
                        @click="showEditProject = true"
                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        <PencilIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
                        Editar Proyecto
                    </button>
                </div>
            </div>
        </template>

        <div class="py-0">
            <div class="w-full">
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    
                    <!-- LEFT COLUMN (Sidebar info) -->
                    <div class="lg:col-span-1 space-y-6 sticky top-6 self-start">
                        
                        <!-- 1. Metadata Card -->
                        <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                            <div class="p-4 space-y-4">
                                <!-- Status -->
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</span>
                                    <span :class="[
                                        statusConfig[project.status]?.bg_light || 'bg-gray-100', 
                                        statusConfig[project.status]?.text || 'text-gray-800',
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium'
                                    ]">
                                        {{ statusConfig[project.status]?.label || project.status }}
                                    </span>
                                </div>

                                <!-- Progress -->
                                <div>
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-500 font-medium">Progreso</span>
                                        <span class="text-gray-900 font-bold">{{ projectProgress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-brand h-2 rounded-full transition-all duration-500" :style="{ width: projectProgress + '%' }"></div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 pt-3 space-y-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-500 flex items-center gap-1">
                                            <CalendarIcon class="w-3.5 h-3.5" /> Inicio
                                        </span>
                                        <span class="text-gray-900 font-medium">{{ formatDate(project.start_date) }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-500 flex items-center gap-1">
                                            <CalendarIcon class="w-3.5 h-3.5" /> Entrega
                                        </span>
                                        <span class="text-gray-900 font-medium">{{ formatDate(project.end_date) }}</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-500 flex items-center gap-1">
                                            <CheckCircleIcon class="w-3.5 h-3.5" /> Tareas Total
                                        </span>
                                        <span class="text-gray-900 font-medium">
                                            {{ project.stages?.reduce((acc, stage) => acc + (stage.tasks?.length || 0), 0) || 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Project Files -->
                        <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                    <PaperClipIcon class="h-4 w-4" />
                                    Archivos del Proyecto
                                </h3>
                            </div>
                            <div v-if="projectFiles.length > 0" class="divide-y divide-gray-100">
                                <template v-for="file in projectFiles" :key="file.id">
                                    <div 
                                        @click="openFilePreview(route('portal.media.show', file.id), file.file_name)"
                                        class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors group flex items-start gap-3"
                                        title="Ver archivo"
                                    >
                                        <DocumentTextIcon class="h-5 w-5 text-gray-400 group-hover:text-brand flex-shrink-0 mt-0.5" />
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-700 group-hover:text-brand truncate">{{ file.file_name }}</p>
                                            <p class="text-xs text-gray-400">{{ (file.size / 1024).toFixed(0) }} KB</p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div v-else class="px-4 py-4 text-center">
                                <p class="text-xs text-gray-400 italic">No hay archivos adjuntos.</p>
                            </div>
                        </div>

 

                        <!-- 4. Invoices (Moved to Sidebar) -->
                        <div v-if="project.invoices && project.invoices.length > 0" class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                             <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                    <CurrencyDollarIcon class="h-4 w-4" />
                                    Facturación
                                </h3>
                            </div>
                            
                            <!-- Financial Summary (if price exists) -->
                            <div v-if="project.price" class="px-4 py-3 bg-white border-b border-gray-100 space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Total</span>
                                    <span class="font-bold text-gray-900">{{ formatCurrency(project.invoices.reduce((sum, inv) => sum + parseFloat(inv.total), 0)) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                     <span class="text-gray-500">Pagado</span>
                                    <span class="font-medium text-green-600">{{ formatCurrency(project.invoices.filter(i => i.status === 'paid').reduce((sum, inv) => sum + parseFloat(inv.total), 0)) }}</span>
                                </div>
                                 <div class="flex justify-between text-xs">
                                     <span class="text-gray-500">Pendiente</span>
                                    <span class="font-medium text-red-600">{{ formatCurrency(project.invoices.filter(i => i.status !== 'paid').reduce((sum, inv) => sum + parseFloat(inv.total), 0)) }}</span>
                                </div>
                            </div>

                            <div class="divide-y divide-gray-100">
                                <div v-for="invoice in project.invoices" :key="invoice.id" class="px-4 py-3 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between mb-1">
                                        <Link :href="route('portal.invoices.show', invoice.id)" class="text-sm font-medium text-brand hover:underline">
                                            {{ invoice.number }}
                                        </Link>
                                        <span :class="[
                                            invoice.status === 'paid' ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50',
                                            'inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-medium'
                                        ]">
                                            {{ invoice.status === 'paid' ? 'Pagada' : 'Pendiente' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <span>{{ formatDate(invoice.date) }}</span>
                                        <span class="font-medium text-gray-900">{{ formatCurrency(invoice.total) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (Main Content) -->
                    <div class="lg:col-span-3 space-y-8">

                        <!-- Description (Moved) -->
                        <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                                    <ChatBubbleLeftIcon class="h-4 w-4" />
                                    Sobre el proyecto
                                </h3>
                            </div>
                            <div class="px-4 py-4">
                                <div class="text-sm text-gray-600 space-y-2">
                                    <p class="whitespace-pre-wrap">{{ project.description || 'Sin descripción disponible.' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stages List -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center justify-center gap-2">
                                    <ClipboardDocumentListIcon class="h-5 w-5 text-gray-400" />
                                    Etapas del Proyecto
                                </h3>
                                <button 
                                    v-if="can('create_stages')"
                                    @click="showAddStageModal = true"
                                    class="inline-flex items-center rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600"
                                >
                                    <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" />
                                    Nueva Etapa
                                </button>
                            </div>
                            
                            <div v-for="stage in project.stages" :key="stage.id" class="bg-white shadow rounded-lg overflow-hidden border border-gray-100">
                                <div class="bg-gray-50/80 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="text-base font-bold text-gray-900">{{ stage.name }}</h3>
                                    <div class="flex items-center gap-2">
                                        <label v-if="can('upload_files')" class="p-1.5 text-gray-400 hover:text-brand rounded cursor-pointer" title="Subir Archivo">
                                            <ArrowUpTrayIcon class="h-5 w-5" />
                                            <input type="file" class="hidden" @change="uploadFileToStage(stage.id, $event)" />
                                        </label>
                                        <button v-if="can('create_tasks')" @click="openCreateTaskSlideOver(stage.id)" class="p-1.5 text-gray-400 hover:text-brand rounded" title="Agregar Tarea">
                                            <PlusIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Stage Files -->
                                <div v-if="stage.media && stage.media.length > 0" class="px-4 py-3 bg-white border-b border-gray-100">
                                    <div class="flex flex-wrap gap-2">
                                        <div 
                                            v-for="file in stage.media" 
                                            :key="file.id"
                                            @click="openFilePreview(route('portal.media.show', file.id), file.file_name)"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-gray-200 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors group"
                                        >
                                            <PaperClipIcon class="h-3.5 w-3.5 text-gray-400 group-hover:text-brand" />
                                            <span class="text-xs font-medium text-gray-700 group-hover:text-brand truncate max-w-[200px]" :title="file.file_name">{{ file.file_name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <ul role="list" class="divide-y divide-gray-100">
                                    <li 
                                        v-for="task in stage.tasks" 
                                        :key="task.id" 
                                        @click="openTask(task)"
                                        class="hover:bg-gray-50 cursor-pointer transition-colors duration-150 group"
                                    >
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <!-- Status Icon -->
                                                    <div class="flex-shrink-0">
                                                        <span 
                                                            :class="[
                                                                task.status === 'completed' ? 'bg-green-100 text-green-600' : 
                                                                task.status === 'in_progress' ? 'bg-blue-100 text-blue-600' : 
                                                                'bg-gray-100 text-gray-400',
                                                                'inline-flex items-center justify-center h-5 w-5 rounded-full'
                                                            ]"
                                                        >
                                                            <CheckCircleIcon v-if="task.status === 'completed'" class="h-3.5 w-3.5" />
                                                            <div v-else class="h-2 w-2 rounded-full bg-current"></div>
                                                        </span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-medium text-brand group-hover:text-brand-dark transition-colors">{{ task.name }}</p>
                                                    </div>
                                                </div>

                                                <div class="ml-4 flex flex-shrink-0 items-center gap-4">
                                                    <!-- Metadata Row (Moved) -->
                                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                                        <div class="flex items-center gap-1">
                                                            <CalendarIcon class="h-3.5 w-3.5 text-gray-400" />
                                                            <span>{{ formatDate(task.due_date) }}</span>
                                                        </div>
                                                        <div v-if="task.comments?.length" class="flex items-center gap-1">
                                                            <ChatBubbleLeftIcon class="h-3.5 w-3.5 text-gray-400" />
                                                            <span>{{ task.comments.length }}</span>
                                                        </div>
                                                        <div v-if="task.media?.length" class="flex items-center gap-1">
                                                            <PaperClipIcon class="h-3.5 w-3.5 text-gray-400" />
                                                            <span>{{ task.media.length }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Priority Badge -->
                                                    <span 
                                                        :class="[
                                                            priorityConfig[task.priority]?.bg, 
                                                            priorityConfig[task.priority]?.color,
                                                            'inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ring-1 ring-inset ring-gray-500/10'
                                                        ]"
                                                    >
                                                        <FlagIcon class="mr-1 h-3 w-3" />
                                                        {{ priorityConfig[task.priority]?.label }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Task Detail SlideOver -->
        <TaskDetailSlideOver
            :show="showSlideOver"
            :task="selectedTask"
            :stage-id="activeStageIdForTask"
            :read-only="selectedTask ? true : !can('create_tasks')"
            :can-create-subtasks="can('create_subtasks')"
            :can-delete="false"
            @close="closeSlideOver"
        />
        
        <!-- Project Edit SlideOver -->
        <ProjectEditSlideOver 
            :show="showEditProject" 
            :project="project" 
            @close="showEditProject = false"
            @preview-file="(file) => openFilePreview(route('portal.media.show', file.id), file.file_name)"
        />

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

        <!-- File Preview Modal -->
        <Modal :show="showFilePreviewModal" @close="closeFilePreview" maxWidth="4xl">
            <div class="p-4 h-[85vh] flex flex-col">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-lg font-medium text-gray-900 truncate pr-4">{{ previewFileName }}</h3>
                    <button @click="closeFilePreview" class="text-gray-400 hover:text-gray-500 focus:outline-none">
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
                 <div class="mt-4 flex justify-end">
                    <a :href="previewFileUrl" download target="_blank" class="text-sm text-brand hover:underline">
                        Descargar archivo original
                    </a>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
