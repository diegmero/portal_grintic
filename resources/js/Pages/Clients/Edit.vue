<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import { PencilSquareIcon } from '@heroicons/vue/24/outline';

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

// User Management Logic
const editingUser = ref(null);
const showUserModal = ref(false);
const userForm = useForm({
    name: '',
    email: '',
    password: '',
});

const editUser = (user) => {
    editingUser.value = user;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.password = ''; // Leave empty if not changing
    showUserModal.value = true;
};

const submitUserUpdate = () => {
    if (!editingUser.value) return;

    userForm.put(route('clients.users.update', [props.client.id, editingUser.value.id]), {
        onSuccess: () => {
             showUserModal.value = false;
             userForm.reset();
             editingUser.value = null;
        }
    });
};
</script>

<template>
    <Head title="Editar Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestionar Cliente: {{ client.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Client Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de la Empresa</h3>
                        <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <InputLabel for="name" value="Razón Social" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
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

                            <div class="md:col-span-2 flex justify-end">
                                <PrimaryButton :disabled="form.processing">Guardar Cambios</PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Associated Users (Contacts) -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Contactos / Usuarios</h3>
                            <!-- Could add "Add User" button here later -->
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in client.users" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <button @click="editUser(user)" class="text-brand hover:text-indigo-900 flex items-center justify-end gap-1 w-full">
                                                <PencilSquareIcon class="h-4 w-4" /> Editar / Clave
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="client.users.length === 0">
                                         <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No hay usuarios asociados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Edit User Modal -->
        <Modal :show="showUserModal" @close="showUserModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Editar Usuario: {{ editingUser?.name }}</h2>
                <div class="space-y-4">
                     <div>
                        <InputLabel for="user_name" value="Nombre Completo" />
                        <TextInput id="user_name" type="text" class="mt-1 block w-full" v-model="userForm.name" />
                        <InputError :message="userForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel for="user_email" value="Correo Electrónico" />
                        <TextInput id="user_email" type="email" class="mt-1 block w-full" v-model="userForm.email" />
                        <InputError :message="userForm.errors.email" />
                    </div>
                     <div>
                        <InputLabel for="user_password" value="Nueva Contraseña (Opcional)" />
                        <TextInput id="user_password" type="password" class="mt-1 block w-full" v-model="userForm.password" placeholder="Dejar en blanco para mantener actual" />
                         <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres.</p>
                        <InputError :message="userForm.errors.password" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showUserModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submitUserUpdate" :disabled="userForm.processing">Actualizar Usuario</PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
