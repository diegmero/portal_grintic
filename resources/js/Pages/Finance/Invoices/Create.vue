<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import InputError from '@/Components/InputError.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    projects: Array,
    companies: Array,
    prefill: Object,
});

const form = useForm({
    company_id: props.prefill?.company_id || '',
    project_id: props.prefill?.project_id || '',
    date: props.prefill?.date || '',
    due_date: props.prefill?.due_date || '',
    items: props.prefill?.items?.length ? props.prefill.items : [
        { description: '', quantity: 1, price: 0 } // Default empty item
    ],
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, price: 0 });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (item.quantity * item.price), 0);
});

// Auto-select company if project is selected
watch(() => form.project_id, (newVal) => {
    if (newVal) {
        const project = props.projects.find(p => p.id === newVal);
        if (project) {
            form.company_id = project.company_id;
        }
    }
});

const submit = () => {
    form.post(route('invoices.store'));
};
</script>

<template>
    <Head title="Nueva Factura" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva Factura</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 text-gray-900 space-y-6">
                        
                        <!-- Header Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cliente (Empresa)</label>
                                <CustomSelect
                                    v-model="form.company_id"
                                    :options="[{ value: '', label: 'Seleccione un cliente' }, ...companies.map(c => ({ value: c.id, label: c.name }))]"
                                    placeholder="Seleccione un cliente"
                                    class="mt-1"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Proyecto (Opcional)</label>
                                <CustomSelect
                                    v-model="form.project_id"
                                    :options="[{ value: '', label: 'Sin Proyecto' }, ...projects.map(p => ({ value: p.id, label: p.name }))]"
                                    placeholder="Sin Proyecto"
                                    class="mt-1"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha Emisión</label>
                                <input type="date" v-model="form.date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fecha Vencimiento</label>
                                <input type="date" v-model="form.due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm">
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items / Servicios</h3>
                            
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
                                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start sm:items-center bg-gray-50 sm:bg-transparent p-4 sm:p-0 rounded-lg">
                                    <div class="sm:col-span-6">
                                        <input type="text" v-model="item.description" placeholder="Descripción del servicio" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" required>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="number" v-model="item.quantity" min="0.01" step="0.01" class="block w-full text-right rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" required placeholder="Cant.">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input type="number" v-model="item.price" min="0" step="0.01" class="block w-full text-right rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm" required placeholder="Precio">
                                    </div>
                                    <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-2">
                                        <span class="text-sm font-medium text-gray-900 sm:w-20 text-right">
                                            $ {{ (item.quantity * item.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                        </span>
                                        <button type="button" @click="removeItem(index)" class="text-gray-400 hover:text-red-500">
                                            <TrashIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="addItem" class="mt-4 flex items-center text-sm font-medium text-brand hover:text-brand-700">
                                <PlusIcon class="h-4 w-4 mr-1" />
                                Agregar Item
                            </button>
                        </div>

                        <!-- Footer Totals -->
                        <div class="border-t border-gray-200 pt-6 flex justify-end">
                            <div class="w-full sm:w-1/3 space-y-2">
                                <div class="flex justify-between text-base font-semibold text-gray-900">
                                    <span>Total:</span>
                                    <span>$ {{ subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-6">
                            <Link :href="route('invoices.index')" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-brand border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-600 focus:bg-brand-600 active:bg-brand-900 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-2 transition ease-in-out duration-150">
                                Guardar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
