<script setup>
import { ref, watch } from 'vue';
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
});

const emit = defineEmits(['close']);

const form = useForm({
    company_name: '',
    country: 'Colombia', // Default
    tax_id: '',
    address: '',
    contact_name: '',
    contact_email: '',
});

const taxIdLabel = ref('NIT');

const countries = {
    'Colombia': 'NIT',
    'Argentina': 'CUIT',
    'Mexico': 'RFC',
    'Costa_Rica': 'Cédula Jurídica',
    'USA': 'EIN',
    'Espana': 'NIF/CIF',
};

watch(() => form.country, (newCountry) => {
    taxIdLabel.value = countries[newCountry] || 'Tax ID';
});

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        // Optional: Reset or initialize deferred data here
        setTimeout(() => {
             form.country = 'Colombia'; // Ensure default
        }, 100);
    } else {
        // Deferred clear on close
        setTimeout(() => {
            form.reset();
        }, 500);
    }
});

const submit = () => {
    form.post(route('clients.store'), {
        onSuccess: () => {
            // emitted close handled by parent usually, but we can do it here too
            emit('close');
            // Form reset is handled by the watcher on close
        },
    });
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
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">Nueva Empresa</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-night text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <span class="absolute -inset-2.5" />
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-300">Completa la información para registrar un nuevo cliente.</p>
                                    </div>
                                </div>
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                    
                                    <!-- Company Info -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 border-b pb-2 mb-4">Datos de la Empresa</h3>
                                        
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <InputLabel for="company_name" value="Nombre de la Empresa" />
                                                <TextInput id="company_name" v-model="form.company_name" type="text" class="mt-1 block w-full" required />
                                                <InputError :message="form.errors.company_name" class="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel for="country" value="País" />
                                                <CustomSelect
                                                    v-model="form.country"
                                                    :options="Object.keys(countries).map(c => ({ value: c, label: c.replace('_', ' ') }))"
                                                    class="mt-1"
                                                />
                                                <InputError :message="form.errors.country" class="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel for="tax_id" :value="taxIdLabel" />
                                                <TextInput id="tax_id" v-model="form.tax_id" type="text" class="mt-1 block w-full" />
                                                <InputError :message="form.errors.tax_id" class="mt-2" />
                                            </div>
                                             <div>
                                                <InputLabel for="address" value="Dirección" />
                                                <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full" />
                                                <InputError :message="form.errors.address" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact Info -->
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900 border-b pb-2 mb-4">Contacto Principal</h3>
                                        
                                        <div class="grid grid-cols-1 gap-4">
                                            <div>
                                                <InputLabel for="contact_name" value="Nombre Completo" />
                                                <TextInput id="contact_name" v-model="form.contact_name" type="text" class="mt-1 block w-full" required />
                                                <InputError :message="form.errors.contact_name" class="mt-2" />
                                            </div>
                                             <div>
                                                <InputLabel for="contact_email" value="Correo Electrónico" />
                                                <TextInput id="contact_email" v-model="form.contact_email" type="email" class="mt-1 block w-full" required />
                                                <InputError :message="form.errors.contact_email" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 bg-gray-50 border-t border-gray-200">
                                    <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                    <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Guardar Cliente
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
