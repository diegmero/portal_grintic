<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    CheckCircleIcon, 
    XCircleIcon, 
    ClockIcon, 
    ExclamationTriangleIcon,
    FunnelIcon
} from '@heroicons/vue/24/outline';


const props = defineProps({
    requests: Object,
    filters: Object
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

const labels = {
    pending: 'Pendiente',
    approved: 'Aprobado',
    rejected: 'Rechazado',
    completed: 'Completado'
};

const formatDate = (date) => new Date(date).toLocaleDateString() + ' ' + new Date(date).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

const form = useForm({
    status: '',
    notes: ''
});

const updateStatus = (request, newStatus) => {
    if (!confirm(`¿Estás seguro de cambiar el estado a ${labels[newStatus]}?`)) return;

    form.status = newStatus;
    form.put(route('admin.requests.update', request.id), {
        onSuccess: () => {
            // Optional toast handled globally
        }
    });
};
</script>

<template>
    <Head title="Gestión de Solicitudes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Solicitudes de Servicios</h2>
        </template>

        <div class="py-0">
            <div class="w-full px-auto">
                
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                                <ClockIcon class="h-6 w-6" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Solicitudes</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ requests.total }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                                <ExclamationTriangleIcon class="h-6 w-6" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pendientes</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ requests.data.filter(r => r.status === 'pending').length }} 
                                    <span class="text-xs text-gray-400 font-normal ml-1">Por aprobar</span>
                                </p>
                            </div>
                        </div>
                    </div>
                     <!-- More stats could be added here if backend provided totals -->
                </div>

                <!-- Main Table Container -->
                <!-- Removed overflow-hidden from outer container to allow dropdowns to spill out -->
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <div class="rounded-md"> 
                        <!-- Inner wrapper handles horizontal scroll if needed, but might still clip. 
                             For dropdowns to work 100% in scrollable tables, they need 'fixed' pos or 'teleport'.
                             Workaround: Ensure table has enough height or use 'visible' overflow if possible. 
                             Actually, if we just remove overflow-hidden from the card, the dropdown will show 
                             UNLESS the table itself forces scroll. 
                             Let's try removing overflow-hidden from the card first. -->
                        
                        <table class="min-w-full divide-y divide-gray-200">
                           <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio Solicitado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extras</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Gestión</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="req in requests.data" :key="req.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-brand flex items-center justify-center text-white text-xs font-bold mr-3">
                                                {{ req.user?.name?.charAt(0) || 'U' }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ req.user?.name || 'Usuario Eliminado' }}</div>
                                                <div class="text-sm text-gray-500">{{ req.user?.email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-medium">{{ req.product?.name }}</div>
                                        <div class="text-xs text-gray-500">{{ formatDate(req.created_at) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                         <div v-if="req.configuration && req.configuration.length" class="flex flex-wrap gap-1 max-w-xs">
                                            <span v-for="addon in req.configuration" :key="addon.id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ addon.name }}
                                            </span>
                                        </div>
                                        <span v-else class="text-xs text-gray-400">Sin extras</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                        ${{ req.total_price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusBadge(req.status)">
                                            {{ labels[req.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                v-if="req.status === 'pending'"
                                                @click="updateStatus(req, 'approved')" 
                                                class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                                title="Aprobar Solicitud"
                                            >
                                                <CheckCircleIcon class="h-5 w-5" />
                                            </button>
                                            <button 
                                                v-if="req.status === 'pending'"
                                                @click="updateStatus(req, 'rejected')" 
                                                class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                                title="Rechazar Solicitud"
                                            >
                                                <XCircleIcon class="h-5 w-5" />
                                            </button>
                                            <button 
                                                v-if="req.status === 'approved'"
                                                @click="updateStatus(req, 'completed')" 
                                                class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                                            >
                                                Marca Completado
                                            </button>
                                             <span v-if="['rejected', 'completed'].includes(req.status)" class="text-xs text-gray-400 italic">
                                                Sin acciones
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <!-- Pagination -->
                    <div v-if="requests.links.length > 3" class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6 rounded-b-lg">
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
