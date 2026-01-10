<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CreateClientSlideOver from '@/Components/Clients/CreateClientSlideOver.vue';
import { 
    PlusIcon, 
    MapPinIcon, 
    BriefcaseIcon, 
    ArrowRightIcon,
    BuildingOffice2Icon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    clients: Array,
});

const showCreateSlideOver = ref(false);

const checkUrlParams = () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('create')) {
        showCreateSlideOver.value = true;
    }
};

onMounted(() => {
    checkUrlParams();
});

// Helper for avatars
const getAvatarUrl = (name) => {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0082c9&color=fff&bold=true`;
};
</script>

<template>
    <Head title="Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Cartera de Clientes</h2>
                    <p class="text-sm text-gray-500 mt-1">Gestiona tus empresas, contactos y proyectos asociados.</p>
                </div>
            </div>
        </template>

        <div class="py-0">
            
            <!-- Grid Layout -->
            <div v-if="clients.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
                <div v-for="client in clients" :key="client.id" class="group relative bg-white overflow-hidden rounded-xl shadow-sm border border-gray-100 flex flex-col">
                    
                    <!-- Decorative Banner -->
                    <div class="h-16 bg-slate-800"></div>

                    <div class="px-5 pt-0 pb-4 flex-1 flex flex-col">
                        <!-- Avatar / Logo -->
                        <div class="-mt-8 mb-3 flex justify-between items-end">
                            <div class="h-14 w-14 rounded-xl bg-white p-1 shadow-sm">
                                <img :src="getAvatarUrl(client.name)" :alt="client.name" class="h-full w-full rounded-lg object-cover bg-gray-50" />
                            </div>
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                {{ client.country }}
                            </span>
                        </div>

                        <!-- Info -->
                        <div class="mb-3">
                            <h3 class="text-base font-bold text-gray-900 truncate leading-tight" :title="client.name">
                                {{ client.name }}
                            </h3>
                            <div class="flex items-center text-[10px] text-gray-500 mt-1 gap-2">
                                <span class="truncate font-mono bg-gray-50 px-1 py-0.5 rounded border border-gray-100">{{ client.tax_id || 'N/A' }}</span>
                                <span v-if="client.address" class="flex items-center truncate max-w-[60%]">
                                    <MapPinIcon class="h-3 w-3 mr-0.5 flex-shrink-0" />
                                    {{ client.address }}
                                </span>
                            </div>
                        </div>

                        <!-- Metrics Grid -->
                        <div class="grid grid-cols-2 gap-2 py-2 border-t border-dashed border-gray-100 mt-auto">
                            <div class="text-center p-1.5 rounded-md bg-gray-50 transition-colors">
                                <p class="text-[10px] text-gray-500 font-medium">Proyectos</p>
                                <p class="text-sm font-bold text-gray-900">{{ client.projects_count }}</p>
                            </div>
                            <div class="text-center p-1.5 rounded-md bg-gray-50 transition-colors">
                                <p class="text-[10px] text-gray-500 font-medium whitespace-nowrap">Estado</p>
                                <span class="inline-flex items-center rounded-full bg-green-700 px-1.5 py-0.5 text-[10px] font-medium text-white">
                                    Activo
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Footer -->
                    <div class="bg-gray-50 px-5 py-2.5 flex items-center justify-between group-hover:bg-night group-hover:text-white transition-colors duration-100">
                        <span class="text-[10px] font-medium text-gray-500 group-hover:text-white uppercase tracking-wide">Gestionar</span>
                        <Link :href="route('clients.edit', client.id)" class="p-1 rounded-full bg-white text-gray-900 shadow-sm group-hover:bg-white group-hover:text-gray-900 transition-all">
                            <ArrowRightIcon class="h-3.5 w-3.5" />
                        </Link>
                    </div>

                    <!-- Full Card Link Overlay -->
                    <Link :href="route('clients.edit', client.id)" class="absolute inset-0 z-10 focus:outline-none">
                        <span class="sr-only">Ver detalles de {{ client.name }}</span>
                    </Link>
                </div>

                <!-- Add New Card (Ghost) -->
                 <button @click="showCreateSlideOver = true" class="group relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 p-12 hover:border-brand hover:bg-blue-50/30 transition-all duration-300">
                    <div class="rounded-full bg-gray-100 p-4 group-hover:bg-white group-hover:shadow-md transition-all">
                        <PlusIcon class="h-8 w-8 text-gray-400 group-hover:text-brand" />
                    </div>
                    <span class="mt-4 text-sm font-semibold text-gray-500 group-hover:text-brand">Registrar Nueva Empresa</span>
                </button>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-3xl shadow-sm border border-gray-100 text-center">
                <div class="bg-blue-50 p-6 rounded-full mb-4">
                    <BuildingOffice2Icon class="h-16 w-16 text-brand" />
                </div>
                <h3 class="text-xl font-bold text-gray-900">No tienes clientes aún</h3>
                <p class="text-gray-500 mt-2 max-w-sm mx-auto">Comienza agregando tu primera empresa para gestionar proyectos y facturación.</p>
                <div class="mt-8">
                     <PrimaryButton @click="showCreateSlideOver = true" size="lg">
                        <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                        Crear Primer Cliente
                    </PrimaryButton>
                </div>
            </div>

        </div>

        <CreateClientSlideOver :open="showCreateSlideOver" @close="showCreateSlideOver = false" />

    </AuthenticatedLayout>
</template>
