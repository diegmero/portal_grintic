<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { DocumentTextIcon, CalendarIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    invoices: Array,
});

const getStatusColor = (status) => {
    const colors = {
        'draft': 'bg-gray-100 text-gray-700',
        'sent': 'bg-blue-100 text-blue-700',
        'partial': 'bg-yellow-100 text-yellow-700',
        'paid': 'bg-green-100 text-green-700',
        'overdue': 'bg-red-100 text-red-700',
        'void': 'bg-gray-100 text-gray-500',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getStatusLabel = (status) => {
    const labels = {
        'draft': 'Borrador',
        'sent': 'Enviada',
        'partial': 'Pago Parcial',
        'paid': 'Pagada',
        'overdue': 'Vencida',
        'void': 'Anulada',
    };
    return labels[status] || status;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
};

const isOverdue = (invoice) => {
    if (invoice.status === 'paid' || invoice.status === 'void') return false;
    if (!invoice.due_date) return false;
    return new Date(invoice.due_date) < new Date();
};

// Stats computed
const pendingAmount = props.invoices
    .filter(inv => ['sent', 'partial', 'overdue'].includes(inv.status))
    .reduce((sum, inv) => sum + parseFloat(inv.balance_due || 0), 0);

const overdueCount = props.invoices.filter(inv => inv.status === 'overdue' || isOverdue(inv)).length;
</script>

<template>
    <Head title="Mis Finanzas" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold leading-tight text-gray-900">Mis Finanzas</h2>
                <p class="text-sm text-gray-500 mt-1">Consulta tus facturas y estado de pagos.</p>
            </div>
        </template>

        <div class="py-0 space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500">Total Facturas</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ invoices.length }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <p class="text-sm text-gray-500">Saldo Pendiente</p>
                    <p class="text-2xl font-bold text-brand mt-1">{{ formatCurrency(pendingAmount) }}</p>
                </div>
                <div v-if="overdueCount > 0" class="bg-red-50 rounded-xl shadow-sm border border-red-200 p-6">
                    <div class="flex items-center gap-2">
                        <ExclamationTriangleIcon class="h-5 w-5 text-red-500" />
                        <p class="text-sm text-red-600">Facturas Vencidas</p>
                    </div>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ overdueCount }}</p>
                </div>
                <div v-else class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-6">
                    <p class="text-sm text-green-600">Estado</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">Al día ✓</p>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900">Historial de Facturas</h3>
                </div>
                
                <div v-if="invoices.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Factura</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proyecto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <DocumentTextIcon class="h-5 w-5 text-gray-400" />
                                        <span class="font-medium text-gray-900">{{ invoice.number }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ invoice.project?.name || '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(invoice.date) }}
                                </td>
                                <td class="px-6 py-4 text-sm" :class="isOverdue(invoice) ? 'text-red-600 font-medium' : 'text-gray-500'">
                                    {{ formatDate(invoice.due_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right font-medium">
                                    {{ formatCurrency(invoice.total) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right font-semibold" :class="parseFloat(invoice.balance_due) > 0 ? 'text-brand' : 'text-green-600'">
                                    {{ formatCurrency(invoice.balance_due) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[getStatusColor(invoice.status), 'text-xs font-medium px-2.5 py-1 rounded-full']">
                                        {{ getStatusLabel(invoice.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('portal.invoices.show', invoice.id)" class="text-brand hover:text-brand/80 text-sm font-medium">
                                        Ver →
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <DocumentTextIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500">No tienes facturas aún.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
