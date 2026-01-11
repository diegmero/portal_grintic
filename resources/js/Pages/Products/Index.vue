<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { 
    PlusIcon, 
    PencilSquareIcon,
    TrashIcon,
    CubeIcon,
    ServerStackIcon,
    CloudIcon,
    ShieldCheckIcon,
    PuzzlePieceIcon,
    EnvelopeIcon,
    EllipsisHorizontalCircleIcon,
    AcademicCapIcon,
    ServerIcon
} from '@heroicons/vue/24/outline';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
import CreateProductSlideOver from '@/Components/Products/CreateProductSlideOver.vue';

const props = defineProps({
    products: Object,
    categories: Array,
    types: Array,
    billingCycles: Array,
    filters: Object,
});

// Category icons
const categoryIcons = {
    hosting: CubeIcon,
    servers: ServerStackIcon,
    cdn: CloudIcon,
    antivirus: ShieldCheckIcon,
    plugins: PuzzlePieceIcon,
    email: EnvelopeIcon,
    other: EllipsisHorizontalCircleIcon,
};

// Filters
const filterForm = ref({
    category: props.filters.category || '',
    type: props.filters.type || '',
    search: props.filters.search || '',
});

watch(filterForm, (val) => {
    router.get(route('products.index'), val, { 
        preserveState: true, 
        preserveScroll: true,
        replace: true 
    });
}, { deep: true });

// SlideOver
const showSlideOver = ref(false);
const editingProduct = ref(null);

const openCreate = () => {
    editingProduct.value = null;
    showSlideOver.value = true;
};

const openEdit = (product) => {
    editingProduct.value = product;
    showSlideOver.value = true;
};

const closeSlideOver = () => {
    showSlideOver.value = false;
    editingProduct.value = null;
};

const deleteProduct = (product) => {
    if (confirm(`¿Eliminar "${product.name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('products.destroy', product.id));
    }
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0);
};
</script>

<template>
    <Head title="Catálogo de Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Catálogo de Productos</h2>
                    <p class="text-sm text-gray-500 mt-1">Hosting, Servidores, CDN, Plugins y más.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('services.index')" class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50">
                        <ServerIcon class="h-5 w-5" />
                        Ver Servicios
                    </Link>
                    <PrimaryButton @click="openCreate" class="inline-flex items-center gap-2">
                        <PlusIcon class="h-5 w-5" />
                        Nuevo Producto
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <CustomSelect
                                v-model="filterForm.category"
                                :options="[{ value: '', label: 'Todas' }, ...categories]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <CustomSelect
                                v-model="filterForm.type"
                                :options="[{ value: '', label: 'Todos' }, ...types]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input 
                                v-model="filterForm.search"
                                type="text" 
                                placeholder="Nombre del producto..."
                                class="w-full text-sm border-gray-300 rounded-lg focus:ring-brand focus:border-brand"
                            />
                        </div>
                    </div>
                </div>

                <!-- Main: Products Table -->
                <div class="lg:col-span-4">
                    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ciclo</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Base</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-lg bg-brand/10 flex items-center justify-center">
                                                <component :is="categoryIcons[product.category] || CubeIcon" class="h-5 w-5 text-brand" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ product.name }}</div>
                                                <div v-if="!product.is_active" class="text-xs text-red-500">Inactivo</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600">{{ categories.find(c => c.value === product.category)?.label || product.category }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            product.type === 'subscription' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'
                                        ]">
                                            {{ types.find(t => t.value === product.type)?.label || product.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ product.billing_cycle ? billingCycles.find(b => b.value === product.billing_cycle)?.label : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900">
                                        {{ formatCurrency(product.base_price) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEdit(product)" class="text-gray-400 hover:text-brand">
                                                <PencilSquareIcon class="h-5 w-5" />
                                            </button>
                                            <button @click="deleteProduct(product)" class="text-gray-400 hover:text-red-500">
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No hay productos. Crea el primero.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit SlideOver -->
        <CreateProductSlideOver 
            :open="showSlideOver" 
            :product="editingProduct"
            :categories="categories"
            :types="types"
            :billing-cycles="billingCycles"
            @close="closeSlideOver" 
        />
    </AuthenticatedLayout>
</template>
