<script setup>
import { ref, computed, watch } from 'vue';
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
    product: Object,
    categories: Array,
    types: Array,
    billingCycles: Array,
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.product);

const form = useForm({
    name: '',
    category: 'hosting',
    type: 'subscription',
    billing_cycle: 'monthly',
    base_price: '',
    description: '',
    is_active: true,
});

watch(() => props.open, (open) => {
    if (open) {
        // Deferred loading to allow animation to start
        setTimeout(() => {
            if (props.product) {
                form.name = props.product.name;
                form.category = props.product.category;
                form.type = props.product.type;
                form.billing_cycle = props.product.billing_cycle || 'monthly';
                form.base_price = props.product.base_price;
                form.description = props.product.description || '';
                form.is_active = props.product.is_active;
            } else {
                form.reset();
                form.category = 'hosting';
                form.type = 'subscription';
                form.billing_cycle = 'monthly';
                form.is_active = true;
            }
        }, 100);
    } else {
        // Deferred clearing on close
        setTimeout(() => {
           form.reset(); 
        }, 500);
    }
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('products.update', props.product.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        form.post(route('products.store'), {
            onSuccess: () => {
                emit('close');
            },
        });
    }
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
                            <form @submit.prevent="submit" class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <div class="bg-night px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            {{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-night text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <span class="absolute -inset-2.5" />
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-300">Configura los detalles del producto.</p>
                                    </div>
                                </div>
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                    
                                    <div>
                                        <InputLabel for="name" value="Nombre del Producto" />
                                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required placeholder="Ej: Hosting Básico" />
                                        <InputError :message="form.errors.name" class="mt-2" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="category" value="Categoría" />
                                            <CustomSelect
                                                v-model="form.category"
                                                :options="categories"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.category" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="type" value="Tipo" />
                                            <CustomSelect
                                                v-model="form.type"
                                                :options="types"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.type" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="billing_cycle" value="Ciclo de Facturación" />
                                            <CustomSelect
                                                v-model="form.billing_cycle"
                                                :options="billingCycles"
                                                class="mt-1"
                                                :disabled="form.type === 'one_time'"
                                            />
                                            <InputError :message="form.errors.billing_cycle" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="base_price" value="Precio Base (USD)" />
                                            <input 
                                                id="base_price" 
                                                v-model="form.base_price" 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                required 
                                                placeholder="0.00" 
                                            />
                                            <InputError :message="form.errors.base_price" class="mt-2" />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel for="description" value="Descripción (Opcional)" />
                                        <textarea 
                                            id="description" 
                                            v-model="form.description" 
                                            rows="3" 
                                            class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm"
                                            placeholder="Detalles del producto..."
                                        ></textarea>
                                        <InputError :message="form.errors.description" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input 
                                            type="checkbox" 
                                            id="is_active" 
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-brand focus:ring-brand"
                                        />
                                        <InputLabel for="is_active" value="Producto Activo" class="!mb-0" />
                                    </div>

                                </div>
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 gap-3 bg-gray-50 border-t border-gray-200">
                                    <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        {{ isEditing ? 'Actualizar' : 'Crear Producto' }}
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
