<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    PlusIcon, 
    PencilSquareIcon,
    TrashIcon,
    EyeIcon,
    KeyIcon,
    CalendarDaysIcon,
    DocumentTextIcon,
    CubeIcon
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CreateServiceSlideOver from '@/Components/Services/CreateServiceSlideOver.vue';
import ServiceDetailSlideOver from '@/Components/Services/ServiceDetailSlideOver.vue';

const props = defineProps({
    services: Object,
    companies: Array,
    products: Array,
    statuses: Array,
    filters: Object,
});

// Filters
const filterForm = ref({
    company_id: props.filters.company_id || '',
    product_id: props.filters.product_id || '',
    status: props.filters.status || '',
    search: props.filters.search || '',
});

watch(filterForm, (val) => {
    router.get(route('services.index'), val, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, { deep: true });

// SlideOvers
const showCreateSlideOver = ref(false);
const showDetailSlideOver = ref(false);
const editingService = ref(null);
const viewingService = ref(null);

const openCreate = () => {
    editingService.value = null;
    showCreateSlideOver.value = true;
};

const openEdit = (service) => {
    editingService.value = service;
    showCreateSlideOver.value = true;
};

const openDetail = (service) => {
    viewingService.value = service;
    showDetailSlideOver.value = true;
};

const closeCreate = () => {
    showCreateSlideOver.value = false;
    editingService.value = null;
};

const closeDetail = () => {
    showDetailSlideOver.value = false;
    viewingService.value = null;
};

const deleteService = (service) => {
    if (confirm(`¿Eliminar este servicio? Esta acción no se puede deshacer.`)) {
        router.delete(route('services.destroy', service.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' });
};

const statusConfig = {
    active: { label: 'Activo', class: 'bg-green-100 text-green-700' },
    expired: { label: 'Vencido', class: 'bg-red-100 text-red-700' },
    cancelled: { label: 'Cancelado', class: 'bg-gray-100 text-gray-500' },
    suspended: { label: 'Suspendido', class: 'bg-yellow-100 text-yellow-700' },
};
</script>

<template>
    <Head title="Servicios de Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Servicios de Clientes</h2>
                    <p class="text-sm text-gray-500 mt-1">Productos asignados a clientes con credenciales.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('products.index')" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50">
                        <CubeIcon class="h-5 w-5" />
                        Ver Catálogo
                    </Link>
                    <PrimaryButton @click="openCreate" class="inline-flex items-center gap-2">
                        <PlusIcon class="h-5 w-5" />
                        Asignar Servicio
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-0">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                
                <!-- Sidebar: Filters -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-4 rounded-xl shadow-sm ring-1 ring-gray-900/5 space-y-4">
                        <h3 class="font-semibold text-gray-900">Filtros</h3>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
                            <CustomSelect
                                v-model="filterForm.company_id"
                                :options="[{ value: '', label: 'Todos' }, ...companies.map(c => ({ value: c.id, label: c.name }))]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                            <CustomSelect
                                v-model="filterForm.product_id"
                                :options="[{ value: '', label: 'Todos' }, ...products.map(p => ({ value: p.id, label: p.name }))]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <CustomSelect
                                v-model="filterForm.status"
                                :options="[{ value: '', label: 'Todos' }, ...statuses]"
                            />
                        </div>
                    </div>
                </div>

                <!-- Main: Services Table -->
                <div class="lg:col-span-4">
                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vence</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="service in services.data" :key="service.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ service.company?.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm text-gray-900">{{ service.product?.name }}</span>
                                            <KeyIcon v-if="service.credentials" class="h-4 w-4 text-brand" title="Tiene credenciales" />
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-semibold text-gray-900">
                                            {{ formatCurrency(service.custom_price || service.product?.base_price) }}
                                        </span>
                                        <span v-if="service.custom_price && service.custom_price != service.product?.base_price" class="text-xs text-green-600 ml-1">
                                            (especial)
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'text-sm',
                                            service.end_date && new Date(service.end_date) < new Date() ? 'text-red-600 font-semibold' : 'text-gray-600'
                                        ]">
                                            {{ formatDate(service.end_date) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            statusConfig[service.status]?.class || 'bg-gray-100 text-gray-700'
                                        ]">
                                            {{ statusConfig[service.status]?.label || service.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openDetail(service)" class="text-gray-400 hover:text-brand" title="Ver Detalles">
                                                <EyeIcon class="h-5 w-5" />
                                            </button>
                                            <button @click="openEdit(service)" class="text-gray-400 hover:text-brand" title="Editar">
                                                <PencilSquareIcon class="h-5 w-5" />
                                            </button>
                                            <button @click="deleteService(service)" class="text-gray-400 hover:text-red-500" title="Eliminar">
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="services.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No hay servicios asignados. Crea el primero.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit SlideOver -->
        <CreateServiceSlideOver 
            :open="showCreateSlideOver" 
            :service="editingService"
            :companies="companies"
            :products="products"
            :statuses="statuses"
            @close="closeCreate" 
        />

        <!-- Detail SlideOver -->
        <ServiceDetailSlideOver 
            :open="showDetailSlideOver" 
            :service="viewingService"
            :statuses="statuses"
            @close="closeDetail"
            @edit="(s) => { closeDetail(); openEdit(s); }"
        />
    </AuthenticatedLayout>
</template>
