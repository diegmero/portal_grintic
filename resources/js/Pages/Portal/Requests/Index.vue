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

        <div class="py-0">
            <div class="w-full mx-auto">
                
                <!-- Client Stats Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-indigo-50 text-indigo-600">
                            <ClockIcon class="h-6 w-6" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Solicitudes Activas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ requests.data.filter(r => ['pending', 'approved'].includes(r.status)).length }}</p>
                        </div>
                    </div>
                     <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6 flex items-center">
                        <div class="p-3 rounded-full bg-green-50 text-green-600">
                            <CheckCircleIcon class="h-6 w-6" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Servicios Aprobados</p>
                            <p class="text-2xl font-bold text-gray-900">{{ requests.data.filter(r => r.status === 'approved').length }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="requests.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center border-dashed border-2 border-gray-200">
                    <div class="mx-auto h-16 w-16 text-gray-300 mb-6 bg-gray-50 rounded-full flex items-center justify-center">
                        <ClockIcon class="h-8 w-8" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Aún no has solicitado servicios</h3>
                    <p class="mt-2 text-gray-500 max-w-sm mx-auto">Explora nuestro catálogo para encontrar el plan perfecto para tu negocio.</p>
                    <div class="mt-8">
                        <Link :href="route('marketplace.index')" class="inline-flex items-center px-6 py-3 border border-transparent shadow text-base font-medium rounded-full text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-all transform hover:-translate-y-0.5">
                            Explorar Catálogo
                        </Link>
                    </div>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Historial reciente</h3>
                        <Link :href="route('marketplace.index')" class="text-sm text-brand font-medium hover:underline">Nueva Solicitud &rarr;</Link>
                    </div>
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-for="req in requests.data" :key="req.id" class="p-6 hover:bg-gray-50 transition duration-150 ease-in-out group">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center mb-1">
                                        <h3 class="text-lg font-bold leading-6 text-gray-900 truncate group-hover:text-brand transition-colors">
                                            {{ req.product?.name || 'Producto Eliminado' }}
                                        </h3>
                                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border" 
                                            :class="[statusBadge(req.status), req.status === 'approved' ? 'border-green-200' : 'border-transparent']">
                                            {{ statusLabel(req.status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 gap-6">
                                        <div class="flex items-center gap-1.5">
                                            <ClockIcon class="flex-shrink-0 h-4 w-4 text-gray-400" />
                                            <p>Solicitado el <span class="font-medium text-gray-700">{{ formatDate(req.created_at) }}</span></p>
                                        </div>
                                        <div class="font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">
                                            ${{ req.total_price }}
                                        </div>
                                    </div>
                                    <!-- Configuration Summary -->
                                    <div v-if="req.configuration && req.configuration.length" class="mt-3 pl-4 border-l-2 border-gray-200">
                                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold mb-1">Extras Incluidos</p>
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="addon in req.configuration" :key="addon.id" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                + {{ addon.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <!-- Action Button for Client like 'Run Payment' could go here -->
                                     <div v-if="req.status === 'approved'" class="flex flex-col items-end gap-2">
                                         <span class="text-xs text-green-600 font-medium flex items-center gap-1">
                                             <CheckCircleIcon class="h-4 w-4" /> Listo para usar
                                         </span>
                                     </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                    
                    <!-- Pagination -->
                    <div v-if="requests.links.length > 3" class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
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
