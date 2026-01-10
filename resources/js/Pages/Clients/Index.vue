<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CreateClientSlideOver from '@/Components/Clients/CreateClientSlideOver.vue';
import { BuildingOfficeIcon, PlusIcon, UserIcon, MapPinIcon } from '@heroicons/vue/24/outline';

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
                
                <div v-if="clients.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="client in clients" :key="client.id" class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow sm:rounded-xl p-5 border border-gray-100 flex flex-col justify-between">
                        
                        <div>
                            <div class="flex items-start justify-between mb-2">
                                <div class="bg-gray-50 p-2 rounded-lg text-gray-500">
                                    <BuildingOfficeIcon class="h-6 w-6" />
                                </div>
                                <span :class="[client.tax_id ? 'text-green-700 bg-green-50 ring-green-600/20' : 'text-gray-600 bg-gray-50 ring-gray-500/10', 'rounded-md whitespace-nowrap px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset']">
                                    {{ client.country }}
                                </span>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 truncate" :title="client.name">{{ client.name }}</h3>
                            <p class="text-xs text-gray-500 mb-4">{{ client.tax_id || 'Sin ID Fiscal' }}</p>

                            <div v-if="client.address" class="flex items-center text-xs text-gray-500 gap-1 mb-2">
                                <MapPinIcon class="h-3 w-3" />
                                <span class="truncate">{{ client.address }}</span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-50">
                            <div class="flex items-center justify-between text-xs">
                                <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-full font-medium">
                                    {{ client.projects_count }} Proyectos
                                </span>
                                <Link :href="route('clients.edit', client.id)" class="text-brand font-medium hover:underline">
                                    Gestionar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-xl px-6 py-12 text-center">
                    <BuildingOfficeIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No hay clientes</h3>
                    <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva empresa.</p>
                    <div class="mt-6">
                         <PrimaryButton @click="showCreateSlideOver = true">
                            <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                            Nueva Empresa
                        </PrimaryButton>
                    </div>
                </div>

            </div>
        </div>

        <CreateClientSlideOver :open="showCreateSlideOver" @close="showCreateSlideOver = false" />

    </AuthenticatedLayout>
</template>
