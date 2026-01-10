<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { FolderIcon, BanknotesIcon, QueueListIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    projects: Array,
    pending_invoices: Array,
    subscriptions: Array,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};
</script>

<template>
    <Head title="Mi Portal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Mi Portal</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <!-- Welcome Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <h3 class="text-lg font-medium text-gray-900">Bienvenido a tu Espacio de Trabajo</h3>
                    <p class="mt-1 text-gray-500">Aquí encontrarás el estado de tus proyectos, facturas pendientes y servicios contratados.</p>
                </div>

                <!-- Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- My Projects -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="bg-blue-100 p-2 rounded-lg text-blue-600">
                                <FolderIcon class="h-6 w-6" />
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Mis Proyectos Activos</h4>
                        </div>
                        <ul v-if="projects.length > 0" class="space-y-3">
                            <li v-for="project in projects" :key="project.id">
                                <Link :href="route('projects.show', project.id)" class="block hover:bg-gray-50 p-2 rounded -mx-2 transition-colors">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="font-medium text-gray-800">{{ project.name }}</span>
                                        <span class="text-gray-500 text-xs">{{ project.progress }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                        <div class="bg-brand h-1.5 rounded-full" :style="{ width: project.progress + '%' }"></div>
                                    </div>
                                </Link>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500 italic">No tienes proyectos activos.</p>
                    </div>

                    <!-- Pending Invoices -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="bg-red-100 p-2 rounded-lg text-red-600">
                                <BanknotesIcon class="h-6 w-6" />
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Facturas Pendientes</h4>
                        </div>
                        <ul v-if="pending_invoices.length > 0" class="space-y-3">
                            <li v-for="invoice in pending_invoices" :key="invoice.id" class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                                <div>
                                    <p class="font-medium text-gray-900">{{ invoice.number }}</p>
                                    <p class="text-xs text-red-500">Vence: {{ invoice.due_date }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">{{ formatCurrency(invoice.balance_due) }}</p>
                                    <!-- In a real app, Link to Pay -->
                                </div>
                            </li>
                        </ul>
                         <p v-else class="text-sm text-gray-500 italic">Estás al día con tus pagos.</p>
                    </div>

                    <!-- Subscriptions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                         <div class="flex items-center gap-4 mb-4">
                            <div class="bg-green-100 p-2 rounded-lg text-green-600">
                                <QueueListIcon class="h-6 w-6" />
                            </div>
                            <h4 class="text-lg font-semibold text-gray-900">Suscripciones Activas</h4>
                        </div>
                        <ul v-if="subscriptions.length > 0" class="space-y-3">
                            <li v-for="sub in subscriptions" :key="sub.id" class="flex justify-between items-center text-sm bg-gray-50 p-2 rounded">
                                <span class="font-medium text-gray-800">{{ sub.plan_name }}</span>
                                <span class="text-xs bg-white border px-1.5 py-0.5 rounded text-gray-600">{{ sub.billing_cycle }}</span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-gray-500 italic">No hay suscripciones activas.</p>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
