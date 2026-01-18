<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ShieldCheckIcon, PlusIcon, PencilSquareIcon, TrashIcon, UserIcon } from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    admins: Array,
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString();
};

const deleteAdmin = (admin) => {
    if (confirm(`¿Estás seguro de eliminar al administrador ${admin.name}? Esta acción no se puede deshacer.`)) {
        router.delete(route('admins.destroy', admin.id));
    }
};
</script>

<template>
    <Head title="Administradores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 w-full">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Administradores</h2>
                    <p class="text-sm text-gray-500 mt-1">Gestión de usuarios con acceso total a la plataforma.</p>
                </div>
                <div class="flex items-center gap-3 self-start sm:self-auto">
                    <Link :href="route('admins.create')">
                        <PrimaryButton class="inline-flex items-center gap-2">
                            <PlusIcon class="h-5 w-5" />
                            <span class="hidden sm:inline">Nuevo Administrador</span>
                            <span class="sm:hidden">Nuevo</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-0">
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Conexión</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Acciones</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="admin in admins" :key="admin.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold">
                                                        {{ admin.name.charAt(0) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
                                                    <div v-if="admin.id === $page.props.auth.user.id" class="text-xs text-green-600 font-bold"> (Tú)</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">{{ admin.email }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                             <div v-if="admin.last_login_at" class="text-sm text-gray-900">
                                                {{ new Date(admin.last_login_at).toLocaleString() }}
                                             </div>
                                             <span v-else class="text-xs text-gray-400 italic">Nunca</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(admin.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-3">
                                                <Link :href="route('admins.edit', admin.id)" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                    <PencilSquareIcon class="h-5 w-5" />
                                                </Link>
                                                <button 
                                                    v-if="admin.id !== $page.props.auth.user.id"
                                                    @click="deleteAdmin(admin)" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Eliminar"
                                                >
                                                    <TrashIcon class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
    </AuthenticatedLayout>
</template>
