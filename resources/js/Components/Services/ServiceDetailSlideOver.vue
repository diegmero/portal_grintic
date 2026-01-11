<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, PencilSquareIcon, KeyIcon, CalendarDaysIcon, DocumentTextIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    open: Boolean,
    service: Object,
    statuses: Array,
});

const emit = defineEmits(['close', 'edit']);

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC' });
};

const statusConfig = {
    active: { label: 'Activo', class: 'bg-green-100 text-green-700' },
    expired: { label: 'Vencido', class: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Cancelado', class: 'bg-gray-100 text-gray-500' },
    suspended: { label: 'Suspendido', class: 'bg-yellow-100 text-yellow-700' },
};
</script>

<template>
    <TransitionRoot as="template" :show="open">
        <Dialog as="div" class="relative z-50" @close="emit('close')">
            <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
                            <DialogPanel class="pointer-events-auto w-screen max-w-md">
                                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                    <div class="bg-brand px-4 py-6 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <DialogTitle class="text-base font-semibold leading-6 text-white">
                                                Detalles del Servicio
                                            </DialogTitle>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button type="button" class="relative rounded-md bg-brand text-white/80 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="service" class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                        
                                        <!-- Product & Client -->
                                        <div class="bg-gray-50 rounded-xl p-4">
                                            <h3 class="font-bold text-lg text-gray-900">{{ service.product?.name }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">Cliente: {{ service.company?.name }}</p>
                                            <span :class="[
                                                'inline-block mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                statusConfig[service.status]?.class || 'bg-gray-100 text-gray-700'
                                            ]">
                                                {{ statusConfig[service.status]?.label || service.status }}
                                            </span>
                                        </div>

                                        <!-- Price & Dates -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-white border border-gray-200 rounded-xl p-4">
                                                <div class="flex items-center gap-2 text-gray-500 mb-1">
                                                    <CurrencyDollarIcon class="h-4 w-4" />
                                                    <span class="text-xs uppercase font-medium">Precio</span>
                                                </div>
                                                <p class="text-lg font-bold text-gray-900">
                                                    {{ formatCurrency(service.custom_price || service.product?.base_price) }}
                                                </p>
                                                <p v-if="service.custom_price" class="text-xs text-green-600">Precio especial</p>
                                            </div>
                                            <div class="bg-white border border-gray-200 rounded-xl p-4">
                                                <div class="flex items-center gap-2 text-gray-500 mb-1">
                                                    <CalendarDaysIcon class="h-4 w-4" />
                                                    <span class="text-xs uppercase font-medium">Vencimiento</span>
                                                </div>
                                                <p :class="[
                                                    'text-lg font-bold',
                                                    service.end_date && new Date(service.end_date) < new Date() ? 'text-red-600' : 'text-gray-900'
                                                ]">
                                                    {{ formatDate(service.end_date) }}
                                                </p>
                                                <p class="text-xs text-gray-500">Inicio: {{ formatDate(service.start_date) }}</p>
                                            </div>
                                        </div>

                                        <!-- Credentials -->
                                        <div v-if="service.credentials" class="bg-gradient-to-br from-brand/5 to-brand/10 border border-brand/20 rounded-xl p-4">
                                            <div class="flex items-center gap-2 text-brand mb-3">
                                                <KeyIcon class="h-5 w-5" />
                                                <span class="text-sm font-semibold uppercase">Credenciales de Acceso</span>
                                            </div>
                                            <pre class="text-sm text-gray-800 font-mono whitespace-pre-wrap bg-white/50 rounded-lg p-3">{{ service.credentials }}</pre>
                                        </div>

                                        <!-- Notes (only for admin) -->
                                        <div v-if="service.notes" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                            <div class="flex items-center gap-2 text-yellow-700 mb-2">
                                                <DocumentTextIcon class="h-4 w-4" />
                                                <span class="text-xs font-semibold uppercase">Notas Internas</span>
                                            </div>
                                            <p class="text-sm text-gray-700">{{ service.notes }}</p>
                                        </div>

                                    </div>
                                    
                                    <div class="flex flex-shrink-0 justify-end px-4 py-4 gap-3 border-t">
                                        <SecondaryButton @click="emit('close')">Cerrar</SecondaryButton>
                                        <PrimaryButton @click="emit('edit', service)" class="flex items-center gap-2">
                                            <PencilSquareIcon class="h-4 w-4" />
                                            Editar
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
