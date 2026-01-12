<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CubeIcon, CalendarIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    services: Array,
});

const getStatusColor = (status) => {
    const colors = {
        'active': 'bg-green-100 text-green-700',
        'pending': 'bg-yellow-100 text-yellow-700',
        'suspended': 'bg-red-100 text-red-700',
        'cancelled': 'bg-gray-100 text-gray-500',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getStatusLabel = (status) => {
    const labels = {
        'active': 'Activo',
        'pending': 'Pendiente',
        'suspended': 'Suspendido',
        'cancelled': 'Cancelado',
    };
    return labels[status] || status;
};

const getBillingCycleLabel = (cycle) => {
    const labels = {
        'monthly': 'Mensual',
        'quarterly': 'Trimestral',
        'semiannual': 'Semestral',
        'annual': 'Anual',
        'one_time': 'Único',
    };
    return labels[cycle] || cycle;
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

const isRenewingSoon = (service) => {
    if (!service.next_billing_date) return false;
    const daysUntilRenewal = Math.ceil((new Date(service.next_billing_date) - new Date()) / (1000 * 60 * 60 * 24));
    return daysUntilRenewal <= 7 && daysUntilRenewal > 0;
};
</script>

<template>
    <Head title="Mis Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-2xl font-bold leading-tight text-gray-900">Mis Servicios</h2>
                <p class="text-sm text-gray-500 mt-1">Servicios contratados y suscripciones activas.</p>
            </div>
        </template>

        <div class="py-0">
            <!-- Services Grid -->
            <div v-if="services.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="service in services" 
                    :key="service.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
                >
                    <!-- Header -->
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-50 p-2 rounded-lg text-brand">
                                    <CubeIcon class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900">{{ service.product?.name || service.name }}</h3>
                                    <p class="text-xs text-gray-500">{{ service.product?.category || 'Servicio' }}</p>
                                </div>
                            </div>
                            <span :class="[getStatusColor(service.status), 'text-xs font-medium px-2 py-1 rounded-full']">
                                {{ getStatusLabel(service.status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-6 space-y-4">
                        <!-- Pricing -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Precio</span>
                            <div class="text-right">
                                <span class="text-lg font-bold text-gray-900">{{ formatCurrency(service.price) }}</span>
                                <span class="text-xs text-gray-400 ml-1">/ {{ getBillingCycleLabel(service.billing_cycle) }}</span>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 flex items-center gap-1">
                                <CalendarIcon class="h-4 w-4" />
                                Inicio
                            </span>
                            <span class="text-sm text-gray-700">{{ formatDate(service.start_date) }}</span>
                        </div>

                        <div v-if="service.next_billing_date" class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 flex items-center gap-1">
                                <ArrowPathIcon class="h-4 w-4" />
                                Próxima Factura
                            </span>
                            <span :class="['text-sm font-medium', isRenewingSoon(service) ? 'text-orange-600' : 'text-gray-700']">
                                {{ formatDate(service.next_billing_date) }}
                            </span>
                        </div>

                        <!-- Renewal Warning -->
                        <div v-if="isRenewingSoon(service)" class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center">
                            <p class="text-xs text-orange-700 font-medium">
                                ⚡ Se renovará pronto
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-gray-100">
                <div class="bg-blue-50 p-6 rounded-full mb-4">
                    <CubeIcon class="h-12 w-12 text-brand" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Sin servicios contratados</h3>
                <p class="text-gray-500 mt-1 text-center max-w-sm">
                    Actualmente no tienes servicios o suscripciones activas con nosotros.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
