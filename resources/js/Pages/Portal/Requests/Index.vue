<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ClockIcon, CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/outline';

defineProps({
    requests: Object
});

const statusBadge = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        case 'completed': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const statusLabel = (status) => {
    const labels = {
        pending: 'Pendiente',
        approved: 'Aprobado',
        rejected: 'Rechazado',
        completed: 'Completado'
    };
    return labels[status] || status;
};

const formatDate = (date) => new Date(date).toLocaleDateString();
</script>

<template>
    <Head title="Mis Solicitudes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis Solicitudes</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="requests.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-10 text-center">
                    <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                        <ClockIcon />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No tienes solicitudes</h3>
                    <p class="mt-1 text-gray-500">¿Necesitas un servicio? Explora nuestro catálogo.</p>
                    <div class="mt-6">
                        <Link :href="route('marketplace.index')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand">
                            Ir al Catálogo
                        </Link>
                    </div>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-for="req in requests.data" :key="req.id" class="p-6 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center mb-1">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900 truncate">
                                            {{ req.product?.name || 'Producto Eliminado' }}
                                        </h3>
                                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="statusBadge(req.status)">
                                            {{ statusLabel(req.status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 gap-4">
                                        <div class="flex items-center gap-1">
                                            <ClockIcon class="flex-shrink-0 h-4 w-4 text-gray-400" />
                                            <p>Solicitado el {{ formatDate(req.created_at) }}</p>
                                        </div>
                                        <div class="font-semibold text-gray-700">
                                            ${{ req.total_price }}
                                        </div>
                                    </div>
                                    <!-- Configuration Summary -->
                                    <div v-if="req.configuration && req.configuration.length" class="mt-2">
                                        <p class="text-xs text-gray-500">Extras incluidos:</p>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span v-for="addon in req.configuration" :key="addon.id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                + {{ addon.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    
                    <!-- Pagination -->
                    <div v-if="requests.links.length > 3" class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
                        <!-- Standard pagination component usage or simple links -->
                         <div class="flex justify-center gap-1">
                            <Link
                                v-for="(link, i) in requests.links"
                                :key="i"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-1 rounded text-sm transition-colors"
                                :class="link.active ? 'bg-brand text-white' : 'text-gray-600 hover:bg-gray-200'"
                                :only="['requests']"
                             />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
