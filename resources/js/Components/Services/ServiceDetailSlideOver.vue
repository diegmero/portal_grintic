<script setup>
import { ref, computed, watch } from 'vue';
import { 
    XMarkIcon, 
    PencilSquareIcon, 
    KeyIcon, 
    CalendarDaysIcon, 
    DocumentTextIcon, 
    CurrencyDollarIcon,
    ClipboardDocumentIcon,
    EyeIcon,
    EyeSlashIcon,
    CheckIcon,
    LinkIcon,
    UserIcon,
    LockClosedIcon
} from '@heroicons/vue/24/outline';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    open: Boolean,
    service: Object,
    statuses: Array,
});

const emit = defineEmits(['close', 'edit']);

const showPassword = ref(false);
const copiedField = ref(null);

// Reset internal state when slide-over opens
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        showPassword.value = false;
    }
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return 'Sin fecha';
    return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' });
};

const statusConfig = {
    active: { label: 'Activo', class: 'bg-green-100 text-green-700' },
    expired: { label: 'Vencido', class: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Cancelado', class: 'bg-gray-100 text-gray-500' },
    suspended: { label: 'Suspendido', class: 'bg-yellow-100 text-yellow-700' },
};

// Parse credentials - handle both string and object
const parsedCredentials = computed(() => {
    if (!props.service?.credentials) return null;
    
    const creds = props.service.credentials;
    if (typeof creds === 'string') {
        try {
            return JSON.parse(creds);
        } catch {
            return { extra: creds };
        }
    }
    return creds;
});

const hasCredentials = computed(() => {
    if (!parsedCredentials.value) return false;
    const c = parsedCredentials.value;
    return c.panel_url || c.username || c.password || c.extra;
});

const copyToClipboard = async (text, fieldName) => {
    try {
        await navigator.clipboard.writeText(text);
        copiedField.value = fieldName;
        setTimeout(() => {
            copiedField.value = null;
        }, 2000);
    } catch (err) {
    }
};

const getInvoiceStatusClass = (status) => {
    switch (status) {
        case 'paid': return 'bg-green-100 text-green-700';
        case 'partial': return 'bg-yellow-100 text-yellow-700';
        case 'overdue': return 'bg-red-100 text-red-700';
        case 'sent': return 'bg-blue-100 text-blue-700';
        default: return 'bg-gray-100 text-gray-500';
    }
};

const translateInvoiceStatus = (status) => {
    const map = {
        'paid': 'Pagado',
        'partial': 'Parcial',
        'overdue': 'Vencido',
        'sent': 'Enviado',
        'draft': 'Borrador',
        'cancelled': 'Cancelado'
    };
    return map[status] || status;
};
</script>

<template>
    <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <Transition
            enter-active-class="ease-in-out duration-500"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in-out duration-500"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="open" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('close')"></div>
        </Transition>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <Transition
                        enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div v-show="open" class="pointer-events-auto w-screen max-w-md">
                            <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <div class="bg-night px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            Detalles del Servicio
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-night text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <span class="absolute -inset-2.5" />
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-300">Información del servicio asignado al cliente.</p>
                                </div>
                                
                                <div v-if="service" class="relative flex-1 px-4 py-6 sm:px-6 space-y-5">
                                    
                                    <!-- Product & Client Header -->
                                    <div class="bg-gray-700 rounded-xl p-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h3 class="font-semibold text-gray-50">{{ service.product?.name }}</h3>
                                                <p class="text-sm text-gray-50 mt-0.5">{{ service.company?.name }}</p>
                                            </div>
                                            <span :class="[
                                                'px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                statusConfig[service.status]?.class || 'bg-gray-100 text-gray-700'
                                            ]">
                                                {{ statusConfig[service.status]?.label || service.status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Billing Status -->
                                    <div v-if="service.invoice_items?.length > 0 && service.invoice_items[0].invoice" class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-2 text-blue-800">
                                                <DocumentTextIcon class="h-4 w-4" />
                                                <span class="text-xs font-semibold uppercase">Última Facturación</span>
                                            </div>
                                            <span :class="[
                                                'px-2 py-0.5 rounded text-xs font-medium uppercase',
                                                getInvoiceStatusClass(service.invoice_items[0].invoice.status)
                                            ]">
                                                {{ translateInvoiceStatus(service.invoice_items[0].invoice.status) }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-end">
                                            <div>
                                                <p class="text-xs text-blue-600">Fecha Emisión</p>
                                                <p class="text-sm font-medium text-blue-900">{{ formatDate(service.invoice_items[0].invoice.date) }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-blue-600">Total Factura</p>
                                                <p class="text-lg font-bold text-blue-900">{{ formatCurrency(service.invoice_items[0].invoice.total) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Price & Dates -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-white border border-gray-200 rounded-xl p-3">
                                            <div class="flex items-center gap-1.5 text-gray-400 mb-1">
                                                <CurrencyDollarIcon class="h-4 w-4" />
                                                <span class="text-xs uppercase font-medium">Precio</span>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ formatCurrency(service.custom_price || service.product?.base_price) }}
                                            </p>
                                            <p v-if="service.custom_price" class="text-xs text-brand">Especial</p>
                                        </div>
                                        <div class="bg-white border border-gray-200 rounded-xl p-3">
                                            <div class="flex items-center gap-1.5 text-gray-400 mb-1">
                                                <CalendarDaysIcon class="h-4 w-4" />
                                                <span class="text-xs uppercase font-medium">Vence</span>
                                            </div>
                                            <p :class="[
                                                'text-sm font-semibold',
                                                service.end_date && new Date(service.end_date) < new Date() ? 'text-red-600' : 'text-gray-900'
                                            ]">
                                                {{ formatDate(service.end_date) }}
                                            </p>
                                            <p class="text-xs text-gray-400">Desde {{ formatDate(service.start_date) }}</p>
                                        </div>
                                    </div>

                                    <!-- Credentials -->
                                    <div v-if="hasCredentials" class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                            <div class="flex items-center gap-2">
                                                <KeyIcon class="h-4 w-4 text-gray-500" />
                                                <span class="text-sm font-medium text-gray-700">Credenciales de Acceso</span>
                                            </div>
                                        </div>
                                        
                                        <div class="divide-y divide-gray-100">
                                            <!-- Panel URL -->
                                            <div v-if="parsedCredentials?.panel_url" class="px-4 py-3 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <LinkIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                                    <div class="min-w-0">
                                                        <p class="text-xs text-gray-400 uppercase">Panel</p>
                                                        <a :href="parsedCredentials.panel_url" target="_blank" class="text-sm text-brand hover:underline truncate block">
                                                            {{ parsedCredentials.panel_url }}
                                                        </a>
                                                    </div>
                                                </div>
                                                <button @click="copyToClipboard(parsedCredentials.panel_url, 'panel')" class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <CheckIcon v-if="copiedField === 'panel'" class="h-4 w-4 text-green-500" />
                                                    <ClipboardDocumentIcon v-else class="h-4 w-4" />
                                                </button>
                                            </div>

                                            <!-- Username -->
                                            <div v-if="parsedCredentials?.username" class="px-4 py-3 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <UserIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                                    <div>
                                                        <p class="text-xs text-gray-400 uppercase">Usuario</p>
                                                        <p class="text-sm text-gray-900 font-mono">{{ parsedCredentials.username }}</p>
                                                    </div>
                                                </div>
                                                <button @click="copyToClipboard(parsedCredentials.username, 'user')" class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <CheckIcon v-if="copiedField === 'user'" class="h-4 w-4 text-green-500" />
                                                    <ClipboardDocumentIcon v-else class="h-4 w-4" />
                                                </button>
                                            </div>

                                            <!-- Password -->
                                            <div v-if="parsedCredentials?.password" class="px-4 py-3 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3">
                                                    <LockClosedIcon class="h-4 w-4 text-gray-400 flex-shrink-0" />
                                                    <div>
                                                        <p class="text-xs text-gray-400 uppercase">Contraseña</p>
                                                        <p class="text-sm text-gray-900 font-mono">
                                                            {{ showPassword ? parsedCredentials.password : '••••••••' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <button @click="showPassword = !showPassword" class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
                                                        <EyeSlashIcon v-if="showPassword" class="h-4 w-4" />
                                                        <EyeIcon v-else class="h-4 w-4" />
                                                    </button>
                                                    <button @click="copyToClipboard(parsedCredentials.password, 'pass')" class="p-1.5 rounded-lg hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
                                                        <CheckIcon v-if="copiedField === 'pass'" class="h-4 w-4 text-green-500" />
                                                        <ClipboardDocumentIcon v-else class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Extra Info -->
                                            <div v-if="parsedCredentials?.extra" class="px-4 py-3">
                                                <p class="text-xs text-gray-400 uppercase mb-1">Información Adicional</p>
                                                <pre class="text-sm text-gray-700 font-mono whitespace-pre-wrap">{{ parsedCredentials.extra }}</pre>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notes (only for admin) -->
                                    <div v-if="service.notes" class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                        <div class="flex items-center gap-2 text-amber-700 mb-2">
                                            <DocumentTextIcon class="h-4 w-4" />
                                            <span class="text-xs font-medium uppercase">Notas Internas</span>
                                        </div>
                                        <p class="text-sm text-gray-700">{{ service.notes }}</p>
                                    </div>

                                </div>
                                
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 gap-3 border-t border-gray-200">
                                    <SecondaryButton @click="emit('close')">Cerrar</SecondaryButton>
                                    <PrimaryButton @click="emit('edit', service)" class="flex items-center gap-2">
                                        <PencilSquareIcon class="h-4 w-4" />
                                        Editar
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
