<script setup>
import { ref, computed, watch } from 'vue';
import { XMarkIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CustomDatePicker from '@/Components/CustomDatePicker.vue';

const props = defineProps({
    open: Boolean,
    service: Object,
    companies: Array,
    products: Array,
    statuses: Array,
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.service);

const form = useForm({
    company_id: '',
    product_id: '',
    custom_price: '',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    status: 'active',
    credentials: {
        panel_url: '',
        username: '',
        password: '',
        extra: '',
    },
    notes: '',
});

const selectedProduct = computed(() => {
    return props.products.find(p => p.id === form.product_id);
});

// Password visibility toggle
const showPassword = ref(false);

// Normalize date to YYYY-MM-DD format
const normalizeDate = (dateStr) => {
    if (!dateStr) return '';
    // If already YYYY-MM-DD, return as is
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) return dateStr;
    // Extract date from ISO format
    const match = dateStr.match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
};

// Form Population Logic
// Consolidated Deferred Watcher
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        // Defer loading to let animation start
        setTimeout(() => {
            if (props.service) {
                // Edit Mode
                const service = props.service;
                form.company_id = service.company_id;
                form.product_id = service.product_id;
                form.custom_price = service.custom_price || '';
                form.start_date = normalizeDate(service.start_date);
                form.end_date = normalizeDate(service.end_date);
                form.status = service.status;
                
                // Parse credentials
                if (service.credentials) {
                    if (typeof service.credentials === 'string') {
                        try {
                            const parsed = JSON.parse(service.credentials);
                            form.credentials = { panel_url: '', username: '', password: '', extra: '', ...parsed };
                        } catch {
                            form.credentials = { panel_url: '', username: '', password: '', extra: service.credentials };
                        }
                    } else {
                        form.credentials = { panel_url: '', username: '', password: '', extra: '', ...service.credentials };
                    }
                } else {
                    form.credentials = { panel_url: '', username: '', password: '', extra: '' };
                }
                form.notes = service.notes || '';
                showPassword.value = false;
            } else {
                // Create Mode
                form.reset();
                form.start_date = new Date().toISOString().split('T')[0];
                form.status = 'active';
                form.credentials = { panel_url: '', username: '', password: '', extra: '' };
                showPassword.value = false;
            }
        }, 100);
    } else {
        // Defer Cleanup
        setTimeout(() => {
            form.reset();
        }, 500);
    }
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('services.update', props.service.id), {
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    } else {
        form.post(route('services.store'), {
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};

// Duration Logic
const durationType = ref('indefinite'); // indefinite, fixed
const durationValue = ref(1);

// When duration type or value changes, or start_date changes, recalculate end_date
watch([durationType, durationValue, () => form.start_date, selectedProduct], ([type, val, startDate, product]) => {
    if (!product || !startDate) return;
    
    // Only auto-calc if user chose Fixed Term
    if (type === 'fixed') {
        // Parse the input date string strictly as local date parts to avoid timezone shift
        const [y, m, d] = startDate.split('-').map(Number);
        
        // Note: Month in JS Date is 0-indexed (0=Jan, 11=Dec)
        // We create a date at 12:00 PM to capture the day safely away from midnight boundaries
        let end = new Date(y, m - 1, d, 12, 0, 0); 
        
        if (product.billing_cycle === 'monthly') {
            // Add N months
            end.setMonth(end.getMonth() + parseInt(val));
        } else if (product.billing_cycle === 'annual') {
            // Add N years
            end.setFullYear(end.getFullYear() + parseInt(val));
        } else {
             return;
        }
        
        // Format back to YYYY-MM-DD using local time parts
        const year = end.getFullYear();
        const month = String(end.getMonth() + 1).padStart(2, '0');
        const day = String(end.getDate()).padStart(2, '0');
        
        form.end_date = `${year}-${month}-${day}`;
    } else if (type === 'indefinite') {
        form.end_date = '';
    }
});
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
                            <form @submit.prevent="submit" class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <div class="bg-night px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            {{ isEditing ? 'Editar Servicio' : 'Asignar Servicio' }}
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-night text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-300">Asigna un producto a un cliente con precio personalizado.</p>
                                </div>
                                
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                    
                                    <!-- Client & Product -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="company_id" value="Cliente" />
                                            <CustomSelect
                                                v-model="form.company_id"
                                                :options="companies.map(c => ({ value: c.id, label: c.name }))"
                                                placeholder="Seleccionar..."
                                                class="mt-1"
                                                :disabled="isEditing"
                                            />
                                            <InputError :message="form.errors.company_id" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="product_id" value="Producto" />
                                            <CustomSelect
                                                v-model="form.product_id"
                                                :options="products.map(p => ({ value: p.id, label: p.name }))"
                                                placeholder="Seleccionar..."
                                                class="mt-1"
                                                :disabled="isEditing"
                                            />
                                            <InputError :message="form.errors.product_id" class="mt-2" />
                                        </div>
                                    </div>

                                    <!-- Price hint -->
                                    <div v-if="selectedProduct" class="bg-blue-50 p-3 rounded-lg text-sm">
                                        <span class="text-gray-600">Precio base:</span>
                                        <span class="font-semibold text-gray-900 ml-1">{{ formatCurrency(selectedProduct.base_price) }}</span>
                                        <span class="text-gray-500 ml-2">{{ selectedProduct.billing_cycle || 'único' }}</span>
                                    </div>

                                    <!-- Precio Especial & Estado -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="custom_price" value="Precio Especial (USD)" />
                                            <input 
                                                id="custom_price" 
                                                v-model="form.custom_price" 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                placeholder="Dejar vacío = base" 
                                            />
                                            <InputError :message="form.errors.custom_price" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="status" value="Estado" />
                                            <CustomSelect
                                                v-model="form.status"
                                                :options="statuses"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.status" class="mt-2" />
                                        </div>
                                    </div>

                                    <!-- Duration Helper -->
                                    <div v-if="selectedProduct && ['monthly', 'annual'].includes(selectedProduct.billing_cycle)" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <InputLabel value="Duración del Servicio" class="mb-2" />
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center">
                                                <input id="dur_indefinite" type="radio" value="indefinite" v-model="durationType" class="h-4 w-4 text-brand border-gray-300 focus:ring-brand">
                                                <label for="dur_indefinite" class="ml-2 block text-sm text-gray-700">Indefinido (Suscripción)</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="dur_fixed" type="radio" value="fixed" v-model="durationType" class="h-4 w-4 text-brand border-gray-300 focus:ring-brand">
                                                <label for="dur_fixed" class="ml-2 block text-sm text-gray-700">Plazo Fijo</label>
                                            </div>
                                        </div>
                                        
                                        <div v-if="durationType === 'fixed'" class="mt-3 flex items-center gap-3">
                                            <div class="w-24">
                                                <input 
                                                    type="number" 
                                                    v-model="durationValue" 
                                                    min="1" 
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm"
                                                >
                                            </div>
                                            <span class="text-sm text-gray-600">
                                                {{ selectedProduct.billing_cycle === 'monthly' ? 'Meses' : 'Años' }}
                                            </span>
                                            <span class="text-xs text-gray-400 italic ml-auto">
                                                Calculando fecha fin...
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Fechas -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="start_date" value="Inicio" />
                                            <CustomDatePicker 
                                                v-model="form.start_date" 
                                                placeholder="Seleccionar..." 
                                                class="mt-1"
                                                :required="true"
                                            />
                                            <InputError :message="form.errors.start_date" class="mt-2" />
                                        </div>
                                        <div>
                                            <InputLabel for="end_date" value="Vencimiento" />
                                            <div class="relative">
                                                <CustomDatePicker 
                                                    v-model="form.end_date" 
                                                    placeholder="Sin vencimiento" 
                                                    class="mt-1"
                                                    :disabled="durationType === 'fixed'"
                                                />
                                                <div v-if="durationType === 'fixed'" class="absolute inset-0 bg-gray-100 opacity-20 cursor-not-allowed"></div>
                                            </div>
                                            <p v-if="durationType === 'fixed'" class="text-xs text-brand mt-1">Calculado automáticamente</p>
                                            <InputError :message="form.errors.end_date" class="mt-2" />
                                        </div>
                                    </div>

                                    <!-- Credentials -->
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel value="Credenciales / Datos de Acceso" />
                                            <p class="text-xs text-gray-500">El cliente podrá ver estos datos.</p>
                                        </div>
                                        
                                        <div>
                                            <InputLabel for="panel_url" value="URL del Panel" />
                                            <input 
                                                id="panel_url" 
                                                v-model="form.credentials.panel_url" 
                                                type="url" 
                                                class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                placeholder="https://panel.ejemplo.com"
                                            />
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="username" value="Usuario" />
                                                <input 
                                                    id="username" 
                                                    v-model="form.credentials.username" 
                                                    type="text" 
                                                    class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                    placeholder="admin"
                                                />
                                            </div>

                                            <div>
                                                <InputLabel for="password" value="Contraseña" />
                                                <div class="relative mt-1">
                                                    <input 
                                                        id="password" 
                                                        v-model="form.credentials.password" 
                                                        :type="showPassword ? 'text' : 'password'" 
                                                        class="block w-full rounded-lg bg-white py-2 pl-3 pr-10 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                        placeholder="••••••••"
                                                    />
                                                    <button 
                                                        type="button" 
                                                        @click="showPassword = !showPassword"
                                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                                    >
                                                        <EyeIcon v-if="!showPassword" class="h-4 w-4" />
                                                        <EyeSlashIcon v-else class="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <InputLabel for="extra" value="Información Adicional" />
                                            <textarea 
                                                id="extra" 
                                                v-model="form.credentials.extra" 
                                                rows="3" 
                                                class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-lg shadow-sm text-sm"
                                                placeholder="FTP, base de datos, etc."
                                            ></textarea>
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <InputLabel for="notes" value="Notas Internas (No visible para cliente)" />
                                        <textarea 
                                            id="notes" 
                                            v-model="form.notes" 
                                            rows="2" 
                                            class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm"
                                            placeholder="Notas privadas..."
                                        ></textarea>
                                        <InputError :message="form.errors.notes" class="mt-2" />
                                    </div>

                                </div>
                                
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 gap-3">
                                    <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        {{ isEditing ? 'Actualizar' : 'Asignar Servicio' }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
