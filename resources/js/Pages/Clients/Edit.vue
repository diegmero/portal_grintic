<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import { 
    PencilSquareIcon, 
    ArrowPathIcon, 
    EyeIcon, 
    EyeSlashIcon, 
    PlusIcon, 
    TrashIcon,
    UsersIcon,
    CurrencyDollarIcon,
    BriefcaseIcon,
    BanknotesIcon,
    BuildingOfficeIcon,
    UserIcon,
    EnvelopeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    client: Object,
    stats: Object,
    projects: Array,
});

const form = useForm({
    name: props.client.name,
    country: props.client.country,
    tax_id: props.client.tax_id,
    currency: props.client.currency,
    address: props.client.address,
    industry: props.client.industry,
    business_type: props.client.business_type,
    status: props.client.status || 'active',
});





const submit = () => {
    form.put(route('clients.update', props.client.id), {
        preserveScroll: true
    });
};

// Tabs Logic
const activeTab = ref('general');
const tabs = [
    { id: 'general', name: 'General', icon: BuildingOfficeIcon },
    { id: 'contacts', name: 'Contactos', icon: UsersIcon },
    { id: 'projects', name: 'Proyectos', icon: BriefcaseIcon },
];

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(value || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};

const statusColors = {
    'active': 'bg-green-100 text-green-700',
    'on_hold': 'bg-yellow-100 text-yellow-700',
    'completed': 'bg-blue-100 text-blue-700',
    'archived': 'bg-gray-100 text-gray-600',
    'cancelled': 'bg-red-100 text-red-700',
};

const statusLabels = {
    'active': 'Activo',
    'on_hold': 'En Pausa',
    'completed': 'Completado',
    'archived': 'Archivado',
    'cancelled': 'Cancelado',
};

const countryOptions = [
    { value: 'Colombia', label: 'Colombia' },
    { value: 'Mexico', label: 'México' },
    { value: 'USA', label: 'Estados Unidos' },
    { value: 'Spain', label: 'España' },
    { value: 'Peru', label: 'Perú' },
    { value: 'Chile', label: 'Chile' },
    { value: 'Argentina', label: 'Argentina' },
    { value: 'Brazil', label: 'Brasil' },
    { value: 'Ecuador', label: 'Ecuador' },
    { value: 'Panama', label: 'Panamá' },
    { value: 'Costa Rica', label: 'Costa Rica' },
];

const industryOptions = [
    { value: 'Agriculture', label: 'Agricultura y Minería' },
    { value: 'Automotive', label: 'Automotriz' },
    { value: 'Construction', label: 'Construcción e Inmobiliaria' },
    { value: 'Consulting', label: 'Consultoría y Servicios Profesionales' },
    { value: 'Education', label: 'Educación' },
    { value: 'Energy', label: 'Energía y Servicios Públicos' },
    { value: 'Financial Services', label: 'Servicios Financieros y Seguros' },
    { value: 'Healthcare', label: 'Salud y Farmacéutica' },
    { value: 'Hospitality', label: 'Hotelería y Turismo' },
    { value: 'IT', label: 'Tecnología y Software (IT)' },
    { value: 'Legal', label: 'Legal' },
    { value: 'Manufacturing', label: 'Manufactura e Industria' },
    { value: 'Media', label: 'Medios y Entretenimiento' },
    { value: 'Non-profit', label: 'Organizaciones Sin Fines de Lucro' },
    { value: 'Retail', label: 'Retail y Comercio Electrónico' },
    { value: 'Logistics', label: 'Transporte y Logística' },
    { value: 'Public Sector', label: 'Sector Público' },
    { value: 'Other', label: 'Otro' },
].sort((a, b) => a.label.localeCompare(b.label));

// User Management Logic
const editingUser = ref(null);
const showUserModal = ref(false);
const isCreatingUser = ref(false);
const showPassword = ref(false);

const userForm = useForm({
    name: '',
    email: '',
    password: '',
    is_active: true,
    is_primary_contact: false,
    permissions: [],
});

const availablePermissions = [
    { id: 'view_projects', label: 'Ver Proyectos' },
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
    userForm.is_active = true;
    userForm.is_primary_contact = false;
    userForm.permissions = [];
    userForm.password = '';
    showPassword.value = false;
    showUserModal.value = true;
};

const editUser = (user) => {
    editingUser.value = user;
    isCreatingUser.value = false;
    userForm.name = user.name;
    userForm.email = user.email;
    userForm.is_active = !!user.is_active;
    userForm.is_primary_contact = !!user.is_primary_contact;
    userForm.permissions = user.permissions ? user.permissions.map(p => p.name) : [];
    userForm.password = '';
    showUserModal.value = true;
};

const submitUserUpdate = () => {
    const url = isCreatingUser.value 
        ? route('clients.users.store', props.client.id)
        : route('clients.users.update', [props.client.id, editingUser.value.id]);
        
    const method = isCreatingUser.value ? 'post' : 'put';

    userForm[method](url, {
        onSuccess: () => {
            showUserModal.value = false;
            userForm.reset();
            editingUser.value = null;
            showPassword.value = false;
            router.reload({ only: ['client'] });
        },
        preserveScroll: true
    });
};

const deleteUser = (user) => {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.')) {
        router.delete(route('clients.users.destroy', [props.client.id, user.id]));
    }
};

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
    <Head :title="client.name" />

    <AuthenticatedLayout>
    
        <div class="space-y-6">
            
            <!-- Breadcrumb & Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol role="list" class="flex items-center space-x-2">
                        <li>
                            <Link :href="route('clients.index')" class="text-sm font-medium text-gray-500 hover:text-gray-700">Clientes</Link>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <span class="text-gray-400 mx-2">/</span>
                                <span class="text-sm font-medium text-gray-900" aria-current="page">{{ client.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Stats Grid -->
            <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="relative overflow-hidden rounded-lg bg-white px-4 pt-5 pb-10 shadow border-t-4 border-indigo-500">
                    <dt>
                        <p class="truncate text-sm font-medium text-gray-500">Total Facturado</p>
                    </dt>
                    <dd class="flex items-baseline mt-1">
                        <p class="text-3xl font-semibold text-gray-900">{{ formatCurrency(stats?.total_invoiced) }}</p>
                    </dd>
                </div>

                <div class="relative overflow-hidden rounded-lg bg-white px-4 pt-5 pb-10 shadow border-t-4 border-red-500">
                    <dt>
                        <p class="truncate text-sm font-medium text-gray-500">Saldo Pendiente</p>
                    </dt>
                    <dd class="flex items-baseline mt-1">
                        <p class="text-3xl font-semibold text-gray-900">{{ formatCurrency(stats?.pending_balance) }}</p>
                    </dd>
                </div>

                <div class="relative overflow-hidden rounded-lg bg-white px-4 pt-5 pb-10 shadow border-t-4 border-brand">
                    <dt>
                        <p class="truncate text-sm font-medium text-gray-500">Proyectos</p>
                    </dt>
                    <dd class="flex items-baseline mt-1">
                        <p class="text-3xl font-semibold text-gray-900">{{ stats?.total_projects }}</p>
                        <p class="ml-2 text-sm text-gray-500 font-medium">({{ stats?.active_projects }} Activos)</p>
                    </dd>
                </div>

                 <div class="relative overflow-hidden rounded-lg bg-white px-4 pt-5 pb-10 shadow border-t-4 border-green-500">
                    <dt>
                        <p class="truncate text-sm font-medium text-gray-500">Total Contactos</p>
                    </dt>
                    <dd class="flex items-baseline mt-1">
                         <p class="text-3xl font-semibold text-gray-900">{{ stats?.total_contacts }}</p>
                    </dd>
                </div>
            </dl>

            <!-- Main Content Area with Tabs -->
            <div class="bg-white rounded-lg shadow min-h-[500px]">
                <!-- Tabs Header -->
                <div class="border-b border-gray-200 px-4 sm:px-6">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id ? 'border-brand text-brand' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 
                                'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                            ]"
                        >
                            <component 
                                :is="tab.icon" 
                                :class="[
                                    activeTab === tab.id ? 'text-brand' : 'text-gray-400 group-hover:text-gray-500', 
                                    '-ml-0.5 mr-2 h-5 w-5'
                                ]" 
                                aria-hidden="true" 
                            />
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    
                    <!-- General Tab -->
                    <div v-show="activeTab === 'general'" class="space-y-6">
                        
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-6">Información de la Empresa</h3>
                            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 max-w-4xl">
                                
                                <div class="col-span-1">
                                    <InputLabel for="name" value="Razón Social *" />
                                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="col-span-1">
                                    <InputLabel for="industry" value="Industria / Sector *" />
                                    <CustomSelect
                                        v-model="form.industry"
                                        :options="industryOptions"
                                        placeholder="Seleccionar Industria..."
                                        class="mt-1 block w-full text-base"
                                        @update:modelValue="submit"
                                    />
                                    <InputError class="mt-2" :message="form.errors.industry" />
                                </div>

                                <div class="col-span-1">
                                    <InputLabel for="tax_id" value="Identificación Fiscal (NIT/RUT)" />
                                    <TextInput id="tax_id" type="text" class="mt-1 block w-full" v-model="form.tax_id" />
                                    <InputError class="mt-2" :message="form.errors.tax_id" />
                                </div>

                                <div class="col-span-1">
                                    <InputLabel for="country" value="País *" />
                                    <CustomSelect
                                        v-model="form.country"
                                        :options="countryOptions"
                                        class="mt-1 block w-full"
                                    />
                                    <InputError class="mt-2" :message="form.errors.country" />
                                </div>
                                
                                <div class="col-span-1">
                                    <InputLabel for="address" value="Dirección" />
                                    <TextInput id="address" type="text" class="mt-1 block w-full" v-model="form.address" />
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>

                                <div class="col-span-1">
                                    <InputLabel for="status" value="Estado del Cliente" />
                                    <CustomSelect
                                        v-model="form.status"
                                        :options="[
                                            { value: 'active', label: 'Activo' },
                                            { value: 'inactive', label: 'Inactivo' },
                                            { value: 'archived', label: 'Archivado' },
                                        ]"
                                        class="mt-1 block w-full"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Si está inactivo, los usuarios de esta empresa no podrán iniciar sesión.
                                    </p>
                                </div>

                                <div class="md:col-span-2 flex justify-end pt-4 border-t border-gray-100">
                                    <PrimaryButton :disabled="form.processing">Guardar Cambios</PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Contacts Tab -->
                    <div v-show="activeTab === 'contacts'">
                         <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Usuarios Asociados ({{ stats.total_contacts }})</h3>
                            <button 
                                @click="createUser"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand hover:bg-brand/90 focus:outline-none shadow-sm"
                            >
                                <PlusIcon class="h-5 w-5 mr-1" /> Nuevo Contacto
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <div 
                                v-for="user in client.users" 
                                :key="user.id" 
                                class="relative group flex flex-col justify-between rounded-lg  border-gray-200 border bg-white p-4 hover:border-brand/50 hover:shadow-md transition-all cursor-pointer"
                                @click="editUser(user)"
                            >
                                <div class="flex items-start gap-3">
                                    <div class="relative flex-shrink-0">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand/10">
                                            <span class="text-base font-bold leading-none text-brand">{{ user.name.charAt(0) }}</span>
                                        </span>
                                        <!-- Primary Contact Badge -->
                                        <div v-if="user.is_primary_contact" class="absolute -top-1 -right-1 bg-yellow-400 rounded-full p-0.5 border border-white" title="Contacto Principal">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-2.5 h-2.5 text-white">
                                              <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-gray-900 truncate pr-1">{{ user.name }}</p>
                                            <span :class="[user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700', 'inline-flex items-center rounded-full px-1.5 py-0.5 text-xs font-medium']">
                                                {{ user.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </div>
                                        <p class="truncate text-xs text-gray-500 mt-0.5">{{ user.email }}</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition-opacity pt-3 border-t border-gray-100">
                                    <button @click.stop="editUser(user)" class="text-xs font-medium text-gray-500 hover:text-brand flex items-center gap-1">
                                        <PencilSquareIcon class="h-3.5 w-3.5" /> Editar
                                    </button>
                                     <button @click.stop="deleteUser(user)" class="text-xs font-medium text-gray-400 hover:text-red-500 flex items-center gap-1">
                                        <TrashIcon class="h-3.5 w-3.5" /> Eliminar
                                    </button>
                                </div>
                            </div>
                             <!-- Empty State -->
                            <div v-if="client.users.length === 0" class="col-span-full text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay contactos</h3>
                                <div class="mt-6">
                                    <button @click="createUser" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand">
                                        <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                                        Agregar Usuario
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Projects Tab -->
                     <div v-show="activeTab === 'projects'">
                         <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Historial de Proyectos</h3>
                             <Link 
                                :href="route('projects.index', { create: true, company_id: client.id })" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand hover:bg-brand/90 focus:outline-none shadow-sm"
                            >
                                <PlusIcon class="h-5 w-5 mr-1" /> Nuevo Proyecto
                            </Link>
                        </div>

                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 sm:pl-6">Nombre</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Estado</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Valor</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Inicio</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Fin Estimado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr 
                                        v-for="project in projects" 
                                        :key="project.id" 
                                        class="hover:bg-gray-50 transition-colors cursor-pointer"
                                        @click="router.visit(route('projects.show', project.id))"
                                    >
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                            {{ project.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span 
                                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                                :class="statusColors[project.status] || 'bg-gray-100 text-gray-800'"
                                            >
                                                {{ statusLabels[project.status] || project.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ formatCurrency(project.price) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ formatDate(project.start_date) }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            {{ formatDate(project.end_date) }}
                                        </td>
                                    </tr>
                                    <tr v-if="!projects || projects.length === 0">
                                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                                            No hay proyectos asignados a este cliente.
                                        </td>
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
             <!-- ... Modal content remains same but good to keep it inside if rewriting whole template ... -->
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
                        <InputError :message="userForm.errors.password" />
                    </div>

                     <!-- Status and Primary Contact -->
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="flex items-center">
                            <input 
                                id="user_active" 
                                type="checkbox" 
                                v-model="userForm.is_active" 
                                class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded" 
                            />
                            <label for="user_active" class="ml-2 block text-sm text-gray-900">
                                Acceso al Sistema (Login)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input 
                                id="user_primary" 
                                type="checkbox" 
                                v-model="userForm.is_primary_contact" 
                                class="h-4 w-4 text-brand focus:ring-brand border-gray-300 rounded" 
                            />
                            <label for="user_primary" class="ml-2 block text-sm text-gray-900">
                                Es Contacto Principal
                            </label>
                        </div>
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
