<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldCheckIcon, UserIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline';

const user = computed(() => usePage().props.auth.user);

// Helpers
const isAdmin = computed(() => {
    return user.value?.roles?.some(role => role.name === 'admin');
});

const roleLabel = computed(() => {
    if (isAdmin.value) return 'Administrador';
    if (user.value?.roles?.some(role => role.name === 'client')) return 'Cliente';
    return 'Sin Rol Asignado';
});

const permissions = computed(() => user.value.permissions || []);
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Rol y Permisos
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Información sobre tu nivel de acceso y permisos en la plataforma.
            </p>
        </header>

        <div class="mt-6 space-y-6">
            <!-- Identity Card -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 flex items-start gap-4">
                <div class="p-2 bg-white rounded-full shadow-sm">
                    <ShieldCheckIcon v-if="isAdmin" class="w-6 h-6 text-brand" />
                    <UserIcon v-else-if="roleLabel === 'Cliente'" class="w-6 h-6 text-blue-500" />
                    <UserIcon v-else class="w-6 h-6 text-gray-400" />
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">{{ roleLabel }}</h3>
                    <div class="mt-1 flex items-center gap-2 text-sm text-gray-500">
                        <BuildingOfficeIcon class="w-4 h-4" />
                        <span v-if="isAdmin">Acceso Global (Grintic)</span>
                        <span v-else>{{ user.company?.name || 'Compañía No Asignada' }}</span>
                    </div>
                </div>
            </div>

            <!-- ID for Debugging -->
            <div class="text-xs font-mono text-gray-400 bg-gray-50 p-2 rounded select-all">
                User ID: {{ user.id }}
            </div>

            <!-- Permissions List (Client Only) -->
            <div v-if="!isAdmin">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Permisos Asignados</h3>
                <div v-if="permissions.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div 
                        v-for="permission in permissions" 
                        :key="permission.id"
                        class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded text-sm text-gray-700"
                    >
                        <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                        {{ permission.name }}
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500 italic bg-yellow-50 p-3 rounded border border-yellow-100">
                    No tienes permisos específicos asignados. Tu acceso podría estar limitado.
                </p>
            </div>

            <div v-else>
                 <p class="text-sm text-gray-500 bg-blue-50 p-3 rounded border border-blue-100">
                    Como Administrador, tienes <strong>acceso total</strong> a todos los módulos y funciones.
                </p>
            </div>
        </div>
    </section>
</template>
