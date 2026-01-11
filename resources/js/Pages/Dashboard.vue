<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { UsersIcon, FolderIcon, BanknotesIcon } from '@heroicons/vue/24/outline';

defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_clients: 0,
            active_projects: 0,
            pending_billing: 0,
        }),
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 text-center w-full">Bienvenido a GrinTic su portal de gestión</h2>
        </template>

        <div class="py-5">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Stats Grid -->
                <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
                    <!-- Clientes Totales -->
                    <div class="overflow-hidden rounded-xl bg-white px-4 py-5 shadow sm:p-6 flex items-center">
                        <div class="rounded-md bg-indigo-500 p-3">
                            <UsersIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="truncate text-sm font-medium text-gray-500">Clientes Totales</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ stats.total_clients }}</dd>
                        </div>
                    </div>

                    <!-- Proyectos Activos -->
                    <div class="overflow-hidden rounded-xl bg-white px-4 py-5 shadow sm:p-6 flex items-center">
                        <div class="rounded-md bg-brand p-3">
                            <FolderIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="truncate text-sm font-medium text-gray-500">Proyectos Activos</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ stats.active_projects }}</dd>
                        </div>
                    </div>

                    <!-- Facturación Pendiente -->
                    <div class="overflow-hidden rounded-xl bg-white px-4 py-5 shadow sm:p-6 flex items-center">
                        <div class="rounded-md bg-green-500 p-3">
                            <BanknotesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="truncate text-sm font-medium text-gray-500">Facturación Pendiente</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ formatCurrency(stats.pending_billing)}}</dd>
                        </div>
                    </div>
                </dl>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
