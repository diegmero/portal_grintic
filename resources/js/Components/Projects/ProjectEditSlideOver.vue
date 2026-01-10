<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { 
    XMarkIcon, 
    PaperClipIcon, 
    TrashIcon, 
    DocumentIcon,
    ArrowUpTrayIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    project: Object,
});

const emit = defineEmits(['close', 'preview-file']);

const form = useForm({
    name: '',
    description: '',
    status: 'active',
    start_date: '',
    due_date: '',
});

const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    return dateString.substring(0, 10);
};

watch(() => props.project, (val) => {
    if (val) {
        form.name = val.name;
        form.description = val.description;
        form.status = val.status;
        form.start_date = val.start_date ? formatDateForInput(val.start_date) : '';
        form.due_date = val.due_date ? formatDateForInput(val.due_date) : '';
    }
}, { immediate: true });

const submit = () => {
    form.put(route('projects.update', props.project.id), {
        onSuccess: () => emit('close'),
    });
};

// File Upload
const isUploading = ref(false);
const uploadProgress = ref(0);
const fileInput = ref(null);

const uploadFile = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('El archivo no puede superar los 5MB.');
        return;
    }

    isUploading.value = true;
    router.post(route('projects.media.store', props.project.id), { file }, {
        forceFormData: true,
        preserveScroll: true,
        onProgress: (progress) => {
            uploadProgress.value = progress.percentage;
        },
        onFinish: () => {
            isUploading.value = false;
            uploadProgress.value = 0;
            fileInput.value.value = '';
        },
        onError: (errors) => {
            isUploading.value = false;
            if (errors.file) alert(errors.file);
        }
    });
};

const deleteFile = (mediaId) => {
    if (confirm('¿Eliminar archivo del proyecto?')) {
        router.delete(route('projects.media.destroy', { project: props.project.id, media: mediaId }), {
            preserveScroll: true,
        });
    }
};

const statusOptions = [
    { value: 'active', label: 'Activo' },
    { value: 'on_hold', label: 'En Pausa' },
    { value: 'completed', label: 'Completado' },
    { value: 'cancelled', label: 'Cancelado' },
];
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
            <div v-show="show" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>
        </Transition>

        <div class="fixed inset-0 overflow-hidden pointer-events-none" v-show="show">
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
                        <div v-show="show" class="pointer-events-auto w-screen max-w-md">
                            <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <div class="px-4 py-6 sm:px-6 bg-gray-50 border-b border-gray-200">
                                    <div class="flex items-start justify-between">
                                        <h2 class="text-lg font-semibold leading-6 text-gray-900" id="slide-over-title">Editar Proyecto</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="rounded-md bg-transparent text-gray-400 hover:text-gray-500 focus:outline-none" @click="$emit('close')">
                                                <XMarkIcon class="h-6 w-6" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Modifica los detalles del proyecto y gestiona sus archivos adjuntos.</p>
                                </div>
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-8">
                                    <!-- Form Info -->
                                    <form @submit.prevent="submit" class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-900">Nombre del Proyecto</label>
                                            <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6" required />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-900">Estado</label>
                                            <select v-model="form.status" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6">
                                                <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                            </select>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900">Fecha Inicio</label>
                                                <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-900">Fecha Entrega</label>
                                                <input v-model="form.due_date" type="date" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6" />
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-900">Descripción / Acta</label>
                                            <textarea v-model="form.description" rows="6" class="mt-1 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6" placeholder="Detalles, acta de entrega, etc..."></textarea>
                                        </div>

                                        <div class="flex justify-end">
                                            <button type="submit" :disabled="form.processing" class="rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                                                Guardar Cambios
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Project Files Section -->
                                    <div class="border-t border-gray-200 pt-6">
                                        <h3 class="text-base font-semibold text-gray-900 mb-4">Archivos del Proyecto</h3>
                                        
                                        <!-- Upload Box -->
                                        <div class="mb-4">
                                            <label class="flex justify-center w-full h-32 px-4 transition bg-white border-2 border-gray-300 border-dashed rounded-md appearance-none cursor-pointer hover:border-brand focus:outline-none relative overflow-hidden">
                                                <span class="flex flex-col items-center justify-center space-y-2 relative z-10">
                                                    <ArrowUpTrayIcon class="w-6 h-6 text-gray-400" />
                                                    <span class="font-medium text-gray-600">
                                                        {{ isUploading ? `Subiendo... ${uploadProgress}%` : 'Click para subir archivo' }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">PDF, Word, Excel (Max 5MB)</span>
                                                </span>
                                                <input ref="fileInput" type="file" name="file_upload" class="hidden" @change="uploadFile" :disabled="isUploading" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                                                
                                                <!-- Progress Bar Background -->
                                                <div v-if="isUploading" class="absolute bottom-0 left-0 h-1 bg-brand transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
                                            </label>
                                        </div>

                                        <!-- Files List -->
                                        <ul v-if="project?.media?.length" class="bg-white rounded-md border border-gray-200 divide-y divide-gray-200">
                                            <li v-for="file in project.media" :key="file.id" class="flex items-center justify-between p-3 text-sm">
                                                <div class="flex items-center flex-1 min-w-0 gap-3 cursor-pointer" @click="$emit('preview-file', file)">
                                                    <DocumentIcon class="h-5 w-5 flex-shrink-0 text-gray-400" />
                                                    <span class="truncate font-medium text-gray-900 hover:text-brand transition-colors">{{ file.file_name }}</span>
                                                </div>
                                                <button @click="deleteFile(file.id)" class="ml-4 text-gray-400 hover:text-red-500">
                                                    <TrashIcon class="h-5 w-5" />
                                                </button>
                                            </li>
                                        </ul>
                                        <p v-else class="text-sm text-gray-500 text-center italic">No hay archivos adjuntos.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
