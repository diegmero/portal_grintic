<script setup>
import { ref, computed } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

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
    due_date: '',
    price: '',
});

const statuses = [
    { value: 'active', label: 'Activo' },
    { value: 'on_hold', label: 'En Pausa' },
    { value: 'completed', label: 'Completado' },
    { value: 'archived', label: 'Archivado' },
    { value: 'cancelled', label: 'Cancelado' },
];

const submit = () => {
    form.post(route('projects.store'), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
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
                                            <DialogTitle class="text-base font-semibold leading-6 text-white">Nuevo Proyecto</DialogTitle>
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
                                                    <TextInput id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.start_date" class="mt-2" />
                                                </div>
                                                <div>
                                                    <InputLabel for="due_date" value="Fecha Entrega" />
                                                    <TextInput id="due_date" v-model="form.due_date" type="date" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.due_date" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="flex flex-shrink-0 justify-end px-4 py-4">
                                        <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                        <PrimaryButton class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            Crear Proyecto
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
