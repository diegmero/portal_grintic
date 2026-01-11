<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { 
    PlusIcon, 
    DocumentTextIcon, 
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    ExclamationCircleIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    invoices: Array,
    companies: Array,
    filters: Object,
    stats: Object,
});

// Config
const statusConfig = {
    draft: { label: 'Borrador', class: 'bg-gray-100 text-gray-700' },
    sent: { label: 'Enviada', class: 'bg-blue-100 text-blue-700' },
    partial: { label: 'Parcial', class: 'bg-yellow-100 text-yellow-700' },
    paid: { label: 'Pagada', class: 'bg-green-100 text-green-700' },
    overdue: { label: 'Vencida', class: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Anulada', class: 'bg-gray-100 text-gray-500 line-through' },
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' });
};

// Filters
const filterForm = ref({
    status: props.filters.status || '',
    company_id: props.filters.company_id || ''
});

watch(filterForm, (val) => {
    router.get(route('invoices.index'), val, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, { deep: true });

</script>

<template>
    <Head title="Finanzas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Finanzas</h2>
                    <p class="text-sm text-gray-500 mt-1">Gestión de facturación, cobros y saldos.</p>
                </div>
            </div>
        </template>

        <div class="py-0">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                
                <!-- Sidebar: Stats & Filters -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Filters -->
                    <div class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-900/5 space-y-4">
                        <h3 class="font-semibold text-gray-900">Filtros</h3>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Cliente</label>
                            <CustomSelect
                                v-model="filterForm.company_id"
                                :options="[{ value: '', label: 'Todos los clientes' }, ...companies.map(c => ({ value: c.id, label: c.name }))]"
                                placeholder="Todos los clientes"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Estado</label>
                            <CustomSelect
                                v-model="filterForm.status"
                                :options="[
                                    { value: '', label: 'Todos los estados' },
                                    { value: 'draft', label: 'Borrador' },
                                    { value: 'sent', label: 'Enviada' },
                                    { value: 'partial', label: 'Parcial' },
                                    { value: 'paid', label: 'Pagada' },
                                    { value: 'overdue', label: 'Vencida' },
                                ]"
                                placeholder="Todos los estados"
                            />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-900/5">
                        <h3 class="font-semibold text-gray-900 mb-2">Acciones</h3>
                        <Link :href="route('invoices.create')" class="w-full justify-center rounded-md bg-brand px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 flex items-center gap-2">
                            <PlusIcon class="h-5 w-5" />
                            Nueva Factura
                        </Link>
                    </div>

                    <!-- Stats -->
                    <div class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-900/5 space-y-4">
                        <h3 class="font-semibold text-gray-900">Resumen Financiero</h3>
                        
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex items-center gap-2">
                                <ArrowTrendingUpIcon class="h-4 w-4 text-gray-500"/>
                                <span class="text-sm text-gray-500">Total</span>
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ formatCurrency(stats.total_amount) }}</span>
                        </div>

                        <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg border border-green-200">
                            <div class="flex items-center gap-2">
                                <CheckCircleIcon class="h-4 w-4 text-green-600"/>
                                <span class="text-sm text-green-700">Pagado</span>
                            </div>
                            <span class="text-sm font-bold text-green-700">{{ formatCurrency(stats.total_paid) }}</span>
                        </div>

                        <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-center gap-2">
                                <ExclamationCircleIcon class="h-4 w-4 text-red-600"/>
                                <span class="text-sm text-red-700">Por Cobrar</span>
                            </div>
                            <span class="text-sm font-bold text-red-700">{{ formatCurrency(stats.total_due) }}</span>
                        </div>
                    </div>

                </div>

                <!-- Main Content: Table -->
                <div class="lg:col-span-4">
                    <div v-if="invoices.length > 0" class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Factura</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fecha</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Cliente / Proyecto</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Total</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Saldo</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                        <span class="">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="invoice in invoices" :key="invoice.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-brand sm:pl-6">
                                        {{ invoice.number }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ formatDate(invoice.date) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                        <div class="font-medium">{{ invoice.company?.name }}</div>
                                        <div class="text-xs text-gray-500" v-if="invoice.project">{{ invoice.project.name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span :class="['inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium', statusConfig[invoice.status]?.class || 'bg-gray-100 text-gray-800']">
                                            {{ statusConfig[invoice.status]?.label || invoice.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 text-right font-medium">
                                        {{ formatCurrency(invoice.total) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-right">
                                        <span :class="invoice.balance_due > 0 ? 'text-red-600 font-medium' : 'text-gray-400'">
                                            {{ formatCurrency(invoice.balance_due) }}
                                        </span>
                                        <div v-if="invoice.balance_due > 0 && new Date(invoice.due_date) < new Date()" class="text-[10px] text-red-500 font-bold uppercase mt-0.5">Vencida</div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <Link :href="route('invoices.show', invoice.id)" class="inline-flex items-center gap-1 text-brand hover:text-brand/80 transition-colors">
                                            <DocumentTextIcon class="h-4 w-4" />
                                            <span>Ver</span>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="bg-blue-50 rounded-full p-6 mx-auto w-fit mb-4">
                            <CurrencyDollarIcon class="h-12 w-12 text-brand" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">No hay facturas</h3>
                        <p class="mt-1 text-sm text-gray-500">Registra tu primera factura para comenzar el seguimiento.</p>
                        <div class="mt-6">
                            <Link :href="route('invoices.create')">
                                <PrimaryButton>
                                    <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                                    Nueva Factura
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
