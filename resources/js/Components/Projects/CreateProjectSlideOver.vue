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
import CustomDatePicker from '@/Components/CustomDatePicker.vue';

const props = defineProps({
    open: Boolean,
    companies: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    company_id: '',
    description: '',
    status: 'active',
    start_date: '',
    end_date: '',
    price: '',
});

const statuses = [
    { value: 'active', label: 'Activo' },
    { value: 'on_hold', label: 'En Pausa' },
    { value: 'completed', label: 'Completado' },
    { value: 'archived', label: 'Archivado' },
    { value: 'cancelled', label: 'Cancelado' },
];

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        // Optional: Reset form on open if needed, or keep previous state
        form.reset();
        form.status = 'active'; 
    }
});

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            emit('close');
            form.reset();
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
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
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">Nuevo Proyecto</h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-night text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <span class="absolute -inset-2.5" />
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-300">Crea un nuevo proyecto y asígnalo a un cliente.</p>
                                    </div>
                                    <div v-if="form.hasErrors" class="mt-4 rounded-md bg-red-50 p-3 border border-red-200">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <XMarkIcon class="h-5 w-5 text-red-400" aria-hidden="true" />
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">Hay errores en el formulario</h3>
                                                <div class="mt-1 text-sm text-red-700">
                                                    <p>Por favor revisa los campos señalados en rojo.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                    
                                    <!-- Project Info -->
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <InputLabel for="name" value="Nombre del Proyecto" />
                                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required />
                                            <InputError :message="form.errors.name" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="company_id" value="Cliente / Empresa" />
                                            <CustomSelect
                                                v-model="form.company_id"
                                                :options="[{ value: '', label: 'Selecciona un cliente' }, ...companies.map(c => ({ value: c.id, label: `${c.name} (${c.tax_id || 'N/A'})` }))]"
                                                placeholder="Selecciona un cliente"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.company_id" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="price" value="Presupuesto / Valor Total" />
                                            <div class="relative mt-1 rounded-md shadow-sm">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <TextInput id="price" v-model="form.price" type="number" step="0.01" class="block w-full pl-7 pr-12" placeholder="0.00" />
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-gray-500 sm:text-sm">USD</span>
                                                </div>
                                            </div>
                                            <InputError :message="form.errors.price" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="description" value="Descripción (Opcional)" />
                                            <textarea id="description" v-model="form.description" class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm" rows="3"></textarea>
                                            <InputError :message="form.errors.description" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="status" value="Estado Inicial" />
                                            <CustomSelect
                                                v-model="form.status"
                                                :options="statuses"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.status" class="mt-2" />
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="start_date" value="Fecha Inicio" />
                                                <CustomDatePicker 
                                                    id="start_date" 
                                                    v-model="form.start_date" 
                                                    placeholder="Seleccionar..." 
                                                    class="mt-1"
                                                />
                                                <InputError :message="form.errors.start_date" class="mt-2" />
                                            </div>
                                            <div>
                                                <InputLabel for="end_date" value="Fecha Entrega" />
                                                <CustomDatePicker 
                                                    id="end_date" 
                                                    v-model="form.end_date" 
                                                    placeholder="Sin fecha" 
                                                    class="mt-1"
                                                />
                                                <InputError :message="form.errors.end_date" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 border-t border-gray-200 bg-gray-50">
                                    <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                    <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        Crear Proyecto
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
