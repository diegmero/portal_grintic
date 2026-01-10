<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CreateClientSlideOver from '@/Components/Clients/CreateClientSlideOver.vue';
import { BuildingOfficeIcon, PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    clients: Array,
});

const showCreateSlideOver = ref(false);

</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Clientes</h2>
                <PrimaryButton @click="showCreateSlideOver = true">
                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                    Nueva Empresa
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl">
                    <ul role="list" class="divide-y divide-gray-100">
                        <li v-for="client in clients" :key="client.id" class="flex items-center justify-between gap-x-6 px-6 py-5 hover:bg-gray-50 transition-colors">
                            <div class="min-w-0">
                                <div class="flex items-start gap-x-3">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ client.name }}</p>
                                    <p :class="[client.tax_id ? 'text-green-700 bg-green-50 ring-green-600/20' : 'text-gray-600 bg-gray-50 ring-gray-500/10', 'rounded-md whitespace-nowrap mt-0.5 px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset']">
                                        {{ client.country }}
                                    </p>
                                </div>
                                <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                                    <p class="truncate">{{ client.tax_id || 'Sin ID Fiscal' }}</p>
                                    <svg viewBox="0 0 2 2" class="h-0.5 w-0.5 fill-current"><circle cx="1" cy="1" r="1" /></svg>
                                    <p class="truncate">{{ client.projects_count }} Proyectos</p>
                                </div>
                            </div>
                            <div class="flex flex-none items-center gap-x-4">
                                <a :href="route('clients.edit', client.id)" class="hidden rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:block">Ver detalles<span class="sr-only">, {{ client.name }}</span></a>
                            </div>
                        </li>
                        <li v-if="clients.length === 0" class="px-6 py-12 text-center">
                            <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay clientes</h3>
                            <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva empresa.</p>
                            <div class="mt-6">
                                <PrimaryButton @click="showCreateSlideOver = true">
                                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                                    Nueva Empresa
                                </PrimaryButton>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <CreateClientSlideOver :open="showCreateSlideOver" @close="showCreateSlideOver = false" />

    </AuthenticatedLayout>
</template>
