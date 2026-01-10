<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { PaperClipIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    taskId: String,
    media: Array,
});

const form = useForm({
    file: null,
});

const isUploading = ref(false);
const fileInput = ref(null);

const triggerUpload = () => {
    fileInput.value.click();
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.file = file;
    isUploading.value = true;

    form.post(route('tasks.media.store', props.taskId), {
        onSuccess: () => {
             form.reset();
             isUploading.value = false;
        },
        onError: () => {
             isUploading.value = false;
        },
    });
};
</script>

<template>
    <div class="mt-4 border-t border-gray-100 pt-3">
        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center justify-between">
            Archivos
            <button @click="triggerUpload" class="text-brand hover:underline flex items-center gap-1">
                <ArrowUpTrayIcon class="h-3 w-3" />
                Subir
            </button>
        </h4>
        
        <input 
            type="file" 
            ref="fileInput" 
            class="hidden" 
            @change="handleFileChange" 
            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
        />

        <div v-if="media && media.length > 0" class="space-y-1">
            <a v-for="file in media" :key="file.id" :href="file.original_url" target="_blank" class="flex items-center gap-2 text-xs text-gray-700 hover:text-brand bg-gray-50 p-1.5 rounded truncate">
                <PaperClipIcon class="h-3.5 w-3.5 flex-shrink-0" />
                <span class="truncate">{{ file.file_name }}</span>
            </a>
        </div>
        <p v-else class="text-[10px] text-gray-400 italic">No hay archivos adjuntos.</p>
        
        <div v-if="isUploading" class="text-[10px] text-brand mt-1 animate-pulse">Subiendo archivo...</div>
    </div>
</template>
