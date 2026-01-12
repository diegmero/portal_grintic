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
import CustomSelect from '@/Components/CustomSelect.vue';
import { PencilSquareIcon, ArrowPathIcon, EyeIcon, EyeSlashIcon, PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';
import { router } from '@inertiajs/vue3';

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
const isCreatingUser = ref(false);
const userForm = useForm({
    name: '',
    email: '',
    email: '',
    password: '',
    permissions: [],
});

const availablePermissions = [
    { id: 'create_stages', label: 'Crear Etapas' },
    { id: 'create_tasks', label: 'Crear Tareas' },
    { id: 'create_subtasks', label: 'Crear Subtareas' },
    { id: 'create_comments', label: 'Comentar' },
    { id: 'upload_files', label: 'Subir Archivos' },
    { id: 'edit_project', label: 'Editar Proyecto (Básico)' },
    { id: 'view_financials', label: 'Ver Finanzas' },
];

const createUser = () => {
    editingUser.value = null;
    isCreatingUser.value = true;
    userForm.reset();
    userForm.permissions = []; // Reset permissions
    userForm.password = ''; // Required for new users
    showPassword.value = false;
    showUserModal.value = true;
};

const editUser = (user) => {
    editingUser.value = user;
    isCreatingUser.value = false;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.permissions = user.permissions ? user.permissions.map(p => p.name) : [];
    userForm.password = ''; // Leave empty if not changing
    showUserModal.value = true;
};

const submitUserUpdate = () => {
    if (isCreatingUser.value) {
        userForm.post(route('clients.users.store', props.client.id), {
            onSuccess: () => {
                showUserModal.value = false;
                userForm.reset();
                showPassword.value = false;
            }
        });
    } else {
        if (!editingUser.value) return;

        userForm.put(route('clients.users.update', [props.client.id, editingUser.value.id]), {
            onSuccess: () => {
                showUserModal.value = false;
                userForm.reset();
                editingUser.value = null;
                showPassword.value = false;
            }
        });
    }
};

const deleteUser = (user) => {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.')) {
        router.delete(route('clients.users.destroy', [props.client.id, user.id]));
    }
};

const showPassword = ref(false);
const generatePassword = () => {
    const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
    let password = "";
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    userForm.password = password;
    showPassword.value = true;
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
                                <CustomSelect
                                    v-model="form.country"
                                    :options="[
                                        { value: 'Colombia', label: 'Colombia' },
                                        { value: 'Mexico', label: 'México' },
                                        { value: 'USA', label: 'Estados Unidos' },
                                        { value: 'Spain', label: 'España' },
                                    ]"
                                    class="mt-1"
                                />
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
                            <button 
                                @click="createUser"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <PlusIcon class="h-4 w-4 mr-1" /> Agregar Usuario
                            </button>
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
                                            <div class="flex justify-end gap-2">
                                                <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                                    <PencilSquareIcon class="h-4 w-4 mr-1" /> Editar
                                                </button>
                                                <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900 flex items-center">
                                                    <TrashIcon class="h-4 w-4 mr-1" /> Eliminar
                                                </button>
                                            </div>
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ isCreatingUser ? 'Crear Nuevo Usuario' : 'Editar Usuario: ' + editingUser?.name }}
                </h2>
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
                        <InputLabel for="user_password" :value="isCreatingUser ? 'Contraseña' : 'Nueva Contraseña (Opcional)'" />
                        <div class="flex items-center gap-2 mt-1">
                            <div class="relative w-full">
                                <TextInput 
                                    id="user_password" 
                                    :type="showPassword ? 'text' : 'password'" 
                                    class="block w-full pr-10" 
                                    v-model="userForm.password" 
                                    placeholder="Dejar en blanco para mantener actual" 
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700"
                                >
                                    <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                                    <EyeSlashIcon v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <button 
                                type="button" 
                                @click="generatePassword" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                title="Generar contraseña segura"
                            >
                                <ArrowPathIcon class="h-6 w-4 mr-1" /> Generar
                            </button>
                        </div>
                         <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres.</p>
                         <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres.</p>
                        <InputError :message="userForm.errors.password" />
                    </div>

                    <!-- Permissions Section -->
                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Permisos de Proyecto</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <label v-for="perm in availablePermissions" :key="perm.id" class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                                <input type="checkbox" :value="perm.id" v-model="userForm.permissions" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span>{{ perm.label }}</span>
                            </label>
                        </div>
                         <p class="text-xs text-gray-500 mt-2 bg-yellow-50 p-2 rounded border border-yellow-100">
                            Nota: Los usuarios clientes <strong>NUNCA</strong> pueden eliminar registros, independientemente de estos permisos.
                        </p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="showUserModal = false">Cancelar</SecondaryButton>
                    <PrimaryButton class="ml-3" @click="submitUserUpdate" :disabled="userForm.processing">
                        {{ isCreatingUser ? 'Crear Usuario' : 'Actualizar Usuario' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
