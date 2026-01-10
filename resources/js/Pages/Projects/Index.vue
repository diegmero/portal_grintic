<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FolderIcon, PlusIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    projects: Array,
});
</script>

<template>
    <Head title="Proyectos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Proyectos</h2>
                <PrimaryButton>
                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                    Nuevo Proyecto
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="project in projects" :key="project.id" :href="route('projects.show', project.id)" class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow transition-transform hover:scale-[1.02]">
                        <div class="flex w-full items-center justify-between space-x-6 p-6">
                            <div class="flex-1 truncate">
                                <div class="flex items-center space-x-3">
                                    <h3 class="truncate text-sm font-medium text-gray-900">{{ project.name }}</h3>
                                    <span class="inline-flex flex-shrink-0 items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{ project.status }}</span>
                                </div>
                                <p class="mt-1 truncate text-sm text-gray-500">{{ project.company ? project.company.name : 'Sin Cliente' }}</p>
                            </div>
                            <div class="h-10 w-10 flex-shrink-0 rounded-full bg-brand flex items-center justify-center text-white">
                                <FolderIcon class="h-6 w-6" />
                            </div>
                        </div>
                        <div class="px-6 py-3">
                             <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-brand h-2.5 rounded-full" :style="{ width: project.progress + '%' }"></div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 text-right">{{ project.progress }}% Completado</div>
                        </div>
                    </Link>
                </div>

                <div v-if="projects.length === 0" class="text-center py-20 bg-white rounded-lg shadow mt-6">
                     <FolderIcon class="mx-auto h-12 w-12 text-gray-400" />
                     <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay proyectos</h3>
                     <p class="mt-1 text-sm text-gray-500">Crea tu primer proyecto para comenzar.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
