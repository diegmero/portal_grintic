<script setup>
import { ref, computed, watch } from 'vue';
import { XMarkIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    open: Boolean,
    companies: Array,
    projects: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    company_id: '',
    project_id: '',
    date: new Date().toISOString().split('T')[0],
    due_date: '',
    items: [
        { description: '', quantity: 1, price: 0 }
    ],
    notes: '',
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, price: 0 });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (item.quantity * item.price), 0);
});

// Auto-select company and due date
watch(() => form.project_id, (newVal) => {
    if (newVal) {
        const project = props.projects.find(p => p.id === newVal);
        if (project) {
            form.company_id = project.company_id;
        }
    }
});

// Fetch active services when company changes
const clientServices = ref([]);
const isLoadingServices = ref(false);
const selectedServiceStart = ref(''); // Control for CustomSelect

const serviceOptions = computed(() => {
    // Group 1: Billable
    const available = clientServices.value.filter(s => s.is_billable).map(s => ({
        value: s.id,
        label: `✅ ${s.product.name} - ${formatCurrency(s.custom_price || s.product.base_price)}`
    }));

    // Group 2: Non-Billable (Disabled/Info)
    const unavailable = clientServices.value.filter(s => !s.is_billable).map(s => ({
        value: s.id,
        label: `🚫 ${s.product.name} (${s.status_message})`,
        disabled: true // CustomSelect needs to handle this, or we just rely on visual cue
    }));

    return [
        { value: '', label: 'Seleccionar servicio para agregar...' },
        ...available,
        ...(unavailable.length > 0 ? [{ value: 'disabled_sep', label: '--- No Disponibles ---', disabled: true }] : []),
        ...unavailable
    ];
});

const filteredProjects = computed(() => {
    if (!form.company_id) return props.projects;
    return props.projects.filter(p => p.company_id === form.company_id);
});

const projectOptions = computed(() => {
    return [
        { value: '', label: 'Sin Proyecto' },
        ...filteredProjects.value.map(p => ({ value: p.id, label: p.name }))
    ];
});

watch(() => form.company_id, async (newVal) => {
    clientServices.value = []; // Clear previous
    
    // Validate current project selection
    if (form.project_id) {
        const currentProject = props.projects.find(p => p.id === form.project_id);
        if (currentProject && currentProject.company_id !== newVal) {
            form.project_id = ''; // Reset if project doesn't belong to new company
        }
    }

    if (newVal) {
        isLoadingServices.value = true;
        try {
            const response = await axios.get(route('clients.services.active', newVal));
            clientServices.value = response.data;
        } catch (error) {
            console.error('Error fetching services:', error);
        } finally {
            isLoadingServices.value = false;
        }
    }
});

// Add service to items
const onServiceSelect = (serviceId) => {
    if (!serviceId || serviceId === 'disabled_sep') return;
    
    // Check if billable check was bypassed
    const service = clientServices.value.find(s => s.id === serviceId);
    if (service && !service.is_billable) {
        alert(service.status_message); // Prevent selection if somehow selected
        selectedServiceStart.value = '';
        return;
    }

    // Check for duplicates
    if (form.items.some(item => item.client_service_id === serviceId)) {
        alert('Este servicio ya está agregado a la factura.');
        selectedServiceStart.value = '';
        return;
    }
    
    addServiceToInvoice(serviceId);
    
    // Reset selector on next tick to allow UI update if needed, though CustomSelect might need null
    setTimeout(() => {
        selectedServiceStart.value = '';
    }, 100);
};

const addServiceToInvoice = (serviceId) => {
    const service = clientServices.value.find(s => s.id === serviceId);
    if (!service) return;

    // Remove empty initial item if it exists and is the only one
    if (form.items.length === 1 && !form.items[0].description && form.items[0].price === 0) {
        form.items.pop();
    }

    form.items.push({
        description: service.product.name + (service.notes ? ` - ${service.notes}` : ''),
        quantity: 1,
        price: service.custom_price || service.product.base_price,
        client_service_id: service.id,
    });
};


// Reset on Open
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        form.reset();
        form.date = new Date().toISOString().split('T')[0];
        // Set default due date (e.g. +30 days)
        const d = new Date();
        d.setDate(d.getDate() + 30);
        form.due_date = d.toISOString().split('T')[0];
        form.items = [{ description: '', quantity: 1, price: 0 }];
        // Reset services list
        clientServices.value = [];
    }
});

const submit = () => {
    if (form.items.length === 0) {
        form.setError('items', 'Debes agregar al menos un item a la factura.');
        return;
    }
    
    if (subtotal.value <= 0) {
         if (!confirm('El total de la factura es $0.00. ¿Deseas continuar?')) {
            return;
         }
    }

    form.post(route('invoices.store'), {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
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
                        <div v-show="open" class="pointer-events-auto w-screen max-w-2xl">
                            <form @submit.prevent="submit" class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <div class="bg-gray-900 px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            Nueva Factura
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-gray-900 text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-300">Crea una nueva factura para cliente o proyecto.</p>
                                </div>
                                
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                    
                                    <!-- Cabecera -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <InputLabel for="company_id" value="Cliente" />
                                            <CustomSelect
                                                v-model="form.company_id"
                                                :options="[{ value: '', label: 'Seleccionar...' }, ...companies.map(c => ({ value: c.id, label: c.name }))]"
                                                placeholder="Seleccionar..."
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.company_id" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="project_id" value="Proyecto (Opcional)" />
                                            <CustomSelect
                                                v-model="form.project_id"
                                                :options="projectOptions"
                                                placeholder="Sin Proyecto"
                                                class="mt-1"
                                                :disabled="!form.company_id && filteredProjects.length === 0"
                                            />
                                            <InputError :message="form.errors.project_id" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="date" value="Fecha Emisión" />
                                            <input 
                                                id="date" 
                                                v-model="form.date" 
                                                type="date" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm"
                                            />
                                            <InputError :message="form.errors.date" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="due_date" value="Fecha Vencimiento" />
                                            <input 
                                                id="due_date" 
                                                v-model="form.due_date" 
                                                type="date" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm"
                                            />
                                            <InputError :message="form.errors.due_date" class="mt-2" />
                                        </div>
                                    </div>

                                    <!-- Items -->
                                    <div class="border-t border-gray-200 pt-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-medium text-gray-900">Items / Servicios</h3>
                                            <button type="button" @click="addItem" class="flex items-center text-xs font-semibold uppercase tracking-wider text-brand hover:text-brand-700">
                                                <PlusIcon class="h-4 w-4 mr-1" />
                                                Agregar Item
                                            </button>
                                        </div>
                                        
                                        <!-- Import Service Dropdown -->
                                        <div v-if="clientServices.length > 0" class="mb-5 bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-xs font-semibold text-blue-700 uppercase tracking-wide">
                                                    Importar Servicio Activo
                                                </label>
                                                <div v-if="isLoadingServices" class="flex items-center gap-1 text-xs text-blue-500">
                                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span>Cargando...</span>
                                                </div>
                                            </div>
                                            
                                            <CustomSelect
                                                :model-value="selectedServiceStart" 
                                                @update:model-value="onServiceSelect"
                                                :options="serviceOptions"
                                                placeholder="Seleccionar servicio para agregar..."
                                                class="w-full"
                                            />
                                        </div>

                                        <InputError :message="form.errors.items" class="mb-4" />

                                        <div class="space-y-4">
                                            <!-- Headers -->
                                            <div class="hidden sm:grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase">
                                                <div class="col-span-6">Descripción</div>
                                                <div class="col-span-2 text-right">Cant.</div>
                                                <div class="col-span-2 text-right">Precio</div>
                                                <div class="col-span-2 text-right">Total</div>
                                            </div>

                                            <!-- Rows -->
                                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start sm:items-center bg-gray-50 sm:bg-transparent p-4 sm:p-0 rounded-lg border sm:border-0 border-gray-200 relative group">
                                                <div class="sm:col-span-6">
                                                    <input 
                                                        type="text" 
                                                        v-model="item.description" 
                                                        placeholder="Descripción..." 
                                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" 
                                                        required
                                                    >
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <input 
                                                        type="number" 
                                                        v-model="item.quantity" 
                                                        min="0.01" 
                                                        step="0.01" 
                                                        class="block w-full text-right rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" 
                                                        required 
                                                        placeholder="Cant."
                                                    >
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <input 
                                                        type="number" 
                                                        v-model="item.price" 
                                                        min="0" 
                                                        step="0.01" 
                                                        class="block w-full text-right rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" 
                                                        required 
                                                        placeholder="Precio"
                                                    >
                                                </div>
                                                <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                                                    <span class="text-sm font-medium text-gray-900 sm:w-20 text-right">
                                                        {{ formatCurrency(item.quantity * item.price) }}
                                                    </span>
                                                    <button type="button" @click="removeItem(index)" class="text-gray-400 hover:text-red-500 transition-colors">
                                                        <TrashIcon class="h-5 w-5" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Notes -->
                                     <div>
                                        <InputLabel for="notes" value="Notas (Opcional)" />
                                        <textarea 
                                            id="notes" 
                                            v-model="form.notes" 
                                            rows="2" 
                                            class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm text-sm"
                                            placeholder="Información adicional..."
                                        ></textarea>
                                    </div>

                                </div>
                                
                                <div class="bg-gray-50 px-4 py-4 sm:px-6 flex items-center justify-between border-t border-gray-200">
                                    <div class="text-lg font-bold text-gray-900">
                                        Total: <span class="text-brand">{{ formatCurrency(subtotal) }}</span>
                                    </div>
                                    <div class="flex gap-3">
                                        <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Guardar Factura
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
