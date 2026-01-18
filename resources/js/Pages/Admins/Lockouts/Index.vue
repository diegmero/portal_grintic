<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ShieldExclamationIcon, ClockIcon } from '@heroicons/vue/24/outline'; // Changed generic icon to ShieldExclamation

defineProps({
    lockouts: Object,
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-ES', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};
</script>

<template>
    <Head title="Intentos Bloqueados" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Intentos Bloqueados</h2>
                <p class="text-sm text-gray-500 mt-1">Registro de bloqueos por exceso de intentos fallidos (Seguridad).</p>
            </div>
        </template>

        <div class="py-0">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl ring-1 ring-gray-900/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Intentado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Agent</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="lockout in lockouts.data" :key="lockout.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <ClockIcon class="h-4 w-4 text-gray-400" />
                                        {{ formatDate(lockout.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ lockout.ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ lockout.email || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" :title="lockout.user_agent">
                                    {{ lockout.user_agent }}
                                </td>
                            </tr>
                            <tr v-if="lockouts.data.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <ShieldExclamationIcon class="h-12 w-12 text-gray-300 mb-3" />
                                        <p>No se han registrado bloqueos recientes.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="lockouts.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                     <!-- Simple pagination links if needed, or use specific pagination component. 
                          For now, just noting it exists. Usually we use a Pagination component. -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
