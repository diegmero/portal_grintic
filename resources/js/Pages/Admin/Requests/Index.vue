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
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

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

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filters could go here -->

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio Solicitado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extras</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
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
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <button class="text-gray-400 hover:text-gray-600">
                                                <span class="sr-only">Opciones</span>
                                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                </svg>
                                            </button>
                                        </template>
                                        <template #content>
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Cambiar Estado
                                            </div>
                                            <button @click="updateStatus(req, 'approved')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" v-if="req.status === 'pending'">
                                                Aprobar Solicitud
                                            </button>
                                            <button @click="updateStatus(req, 'rejected')" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100" v-if="req.status === 'pending'">
                                                Rechazar
                                            </button>
                                            <button @click="updateStatus(req, 'completed')" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-gray-100" v-if="req.status === 'approved'">
                                                Marcar Completado
                                            </button>
                                        </template>
                                    </Dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
