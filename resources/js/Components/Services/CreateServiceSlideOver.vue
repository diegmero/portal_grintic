<script setup>
import { ref, computed, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

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
    credentials: '',
    notes: '',
});

const selectedProduct = computed(() => {
    return props.products.find(p => p.id === form.product_id);
});

watch(() => props.open, (open) => {
    if (open && props.service) {
        form.company_id = props.service.company_id;
        form.product_id = props.service.product_id;
        form.custom_price = props.service.custom_price || '';
        form.start_date = props.service.start_date;
        form.end_date = props.service.end_date || '';
        form.status = props.service.status;
        form.credentials = props.service.credentials || '';
        form.notes = props.service.notes || '';
    } else if (open) {
        form.reset();
        form.start_date = new Date().toISOString().split('T')[0];
        form.status = 'active';
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
                                <form @submit.prevent="submit" class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                    <div class="bg-night px-4 py-6 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <DialogTitle class="text-base font-semibold leading-6 text-white">
                                                {{ isEditing ? 'Editar Servicio' : 'Asignar Servicio' }}
                                            </DialogTitle>
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

                                        <!-- Fechas -->
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="start_date" value="Inicio" />
                                                <input 
                                                    id="start_date" 
                                                    v-model="form.start_date" 
                                                    type="date" 
                                                    class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                    required 
                                                />
                                                <InputError :message="form.errors.start_date" class="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel for="end_date" value="Vencimiento" />
                                                <input 
                                                    id="end_date" 
                                                    v-model="form.end_date" 
                                                    type="date" 
                                                    class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                />
                                                <InputError :message="form.errors.end_date" class="mt-2" />
                                            </div>
                                        </div>

                                        <!-- Credentials -->
                                        <div>
                                            <InputLabel for="credentials" value="Credenciales / Datos de Acceso" />
                                            <p class="text-xs text-gray-500 mb-1">URLs, usuarios, contraseñas. El cliente podrá ver esto.</p>
                                            <textarea 
                                                id="credentials" 
                                                v-model="form.credentials" 
                                                rows="4" 
                                                class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm font-mono text-sm"
                                                placeholder="Panel: https://...&#10;Usuario: ...&#10;Contraseña: ..."
                                            ></textarea>
                                            <InputError :message="form.errors.credentials" class="mt-2" />
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
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
