<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { DocumentTextIcon, PrinterIcon, ArrowLeftIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    invoice: Object,
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
    return new Date(date).toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
};

const printInvoice = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Factura ${invoice.number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('portal.invoices')" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <div>
                        <h2 class="text-2xl font-bold leading-tight text-gray-900">{{ invoice.number }}</h2>
                        <p class="text-sm text-gray-500 mt-1">Detalle de factura</p>
                    </div>
                </div>
                <button @click="printInvoice" class="flex items-center gap-2 px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand/90 transition-colors no-print">
                    <PrinterIcon class="h-5 w-5" />
                    Imprimir
                </button>
            </div>
        </template>

        <div class="py-0">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Invoice Header -->
                <div class="px-8 py-6 border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500">Factura</p>
                            <p class="text-2xl font-bold text-gray-900">{{ invoice.number }}</p>
                        </div>
                        <span :class="[getStatusColor(invoice.status), 'text-sm font-medium px-4 py-2 rounded-full']">
                            {{ getStatusLabel(invoice.status) }}
                        </span>
                    </div>
                </div>

                <!-- Billing Info -->
                <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-2 gap-8 border-b border-gray-100">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Facturado a</p>
                        <p class="font-semibold text-gray-900">{{ invoice.company?.name }}</p>
                        <p class="text-sm text-gray-500">{{ invoice.company?.tax_id }}</p>
                        <p class="text-sm text-gray-500">{{ invoice.company?.address }}</p>
                    </div>
                    <div class="text-right">
                        <div class="mb-3">
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Fecha de Emisión</p>
                            <p class="font-medium text-gray-900">{{ formatDate(invoice.date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide">Fecha de Vencimiento</p>
                            <p class="font-medium text-gray-900">{{ formatDate(invoice.due_date) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Project Reference -->
                <div v-if="invoice.project" class="px-8 py-4 bg-gray-50 border-b border-gray-100">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Proyecto Asociado</p>
                    <p class="font-medium text-gray-900">{{ invoice.project.name }}</p>
                </div>

                <!-- Line Items -->
                <div class="px-8 py-6">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Descripción</th>
                                <th class="py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Cantidad</th>
                                <th class="py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio</th>
                                <th class="py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in invoice.items" :key="item.id">
                                <td class="py-4 text-sm text-gray-900">{{ item.description }}</td>
                                <td class="py-4 text-sm text-gray-500 text-right">{{ item.quantity }}</td>
                                <td class="py-4 text-sm text-gray-500 text-right">{{ formatCurrency(item.price) }}</td>
                                <td class="py-4 text-sm text-gray-900 text-right font-medium">{{ formatCurrency(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="flex justify-end">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Subtotal</span>
                                    <span class="text-sm text-gray-900">{{ formatCurrency(invoice.total) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-100">
                                    <span class="text-base font-semibold text-gray-900">Total</span>
                                    <span class="text-base font-bold text-gray-900">{{ formatCurrency(invoice.total) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-100">
                                    <span class="text-sm text-gray-500">Pagado</span>
                                    <span class="text-sm text-green-600">{{ formatCurrency(invoice.total - invoice.balance_due) }}</span>
                                </div>
                                <div class="flex justify-between bg-brand/5 -mx-4 px-4 py-3 rounded-lg">
                                    <span class="text-base font-semibold text-brand">Saldo Pendiente</span>
                                    <span class="text-base font-bold text-brand">{{ formatCurrency(invoice.balance_due) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments History -->
                <div v-if="invoice.payments?.length" class="px-8 py-6 bg-gray-50 border-t border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-4">Historial de Pagos</h3>
                    <div class="space-y-3">
                        <div v-for="payment in invoice.payments" :key="payment.id" class="flex items-center justify-between bg-white p-4 rounded-lg border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="bg-green-100 p-2 rounded-full">
                                    <CheckCircleIcon class="h-5 w-5 text-green-600" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                                    <p class="text-xs text-gray-500">{{ formatDate(payment.date) }} • {{ payment.method }}</p>
                                </div>
                            </div>
                            <span v-if="payment.reference" class="text-xs text-gray-400 font-mono">{{ payment.reference }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
