<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    client: Object,
});

const form = useForm({
    name: props.client.name,
    country: props.client.country,
    tax_id: props.client.tax_id,
    currency: props.client.currency,
    address: props.client.address,
});

const submit = () => {
    form.put(route('clients.update', props.client.id));
};
</script>

<template>
    <Head title="Editar Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Cliente: {{ client.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form @submit.prevent="submit" class="space-y-6 max-w-xl">
                            
                            <div>
                                <InputLabel for="name" value="Razón Social / Nombre" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="country" value="País" />
                                <select id="country" v-model="form.country" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="Colombia">Colombia</option>
                                    <option value="Mexico">México</option>
                                    <option value="USA">Estados Unidos</option>
                                    <option value="Spain">España</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.country" />
                            </div>

                            <div>
                                <InputLabel for="tax_id" value="Identificación Fiscal" />
                                <TextInput id="tax_id" type="text" class="mt-1 block w-full" v-model="form.tax_id" required />
                                <InputError class="mt-2" :message="form.errors.tax_id" />
                            </div>
                            
                            <div>
                                <InputLabel for="address" value="Dirección" />
                                <TextInput id="address" type="text" class="mt-1 block w-full" v-model="form.address" />
                                <InputError class="mt-2" :message="form.errors.address" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Actualizar Cliente</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
