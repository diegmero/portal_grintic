<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircleIcon } from '@heroicons/vue/24/outline';
import { CheckCircleIcon as CheckCircleIconSolid } from '@heroicons/vue/24/solid';
import CommentsSection from '@/Components/Comments/CommentsSection.vue';
import FileUploader from '@/Components/Files/FileUploader.vue';

const props = defineProps({
    project: Object,
});

const toggleTask = (task) => {
    // Optimistic UI handled by Inertia if backend returns updated state fast, 
    // or we can locally mutate if needed. For now, trusting server response.
    router.patch(route('tasks.update', task.id), {
        is_completed: task.status === 'pending', // Toggle logic
    }, {
        preserveScroll: true,
    });
};

</script>

<template>
    <Head :title="project.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                     <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ project.name }}</h2>
                     <p class="text-sm text-gray-500">{{ project.company ? project.company.name : '' }}</p>
                </div>
                 <div class="w-48">
                    <div class="flex justify-between text-xs mb-1">
                        <span>Progreso</span>
                        <span>{{ project.progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-500 h-2.5 rounded-full transition-all duration-500" :style="{ width: project.progress + '%' }"></div>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stages -->
                <div v-for="stage in project.stages" :key="stage.id" class="bg-gray-50 rounded-xl p-4 border border-gray-200/60">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-2 h-8 bg-brand rounded-full"></span>
                        {{ stage.name }}
                    </h3>

                    <div class="space-y-3">
                        <div v-for="task in stage.tasks" :key="task.id" class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <button @click="toggleTask(task)" class="mt-1 flex-shrink-0 text-gray-400 hover:text-green-500 transition-colors">
                                    <CheckCircleIconSolid v-if="task.status === 'completed'" class="h-6 w-6 text-green-500" />
                                    <CheckCircleIcon v-else class="h-6 w-6" />
                                </button>
                                <div class="flex-1">
                                    <h4 :class="['text-sm font-medium', task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900']">
                                        {{ task.name }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ task.description }}</p>
                                    
                                    <CommentsSection 
                                        :commentable-id="task.id" 
                                        commentable-type="App\Models\Task" 
                                        :initial-comments="task.comments"
                                    />

                                    <FileUploader :task-id="task.id" :media="task.media" />
                                </div>
                                <div class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                    w:{{ task.weight }}
                                </div>
                            </div>
                        </div>
                         <div v-if="stage.tasks.length === 0" class="text-sm text-gray-400 italic pl-4">No tasks in this stage.</div>
                    </div>
                </div>

                <div v-if="project.stages.length === 0" class="text-center py-10">
                    <p class="text-gray-500">No outlines defined for this project.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
