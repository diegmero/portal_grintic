<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { MagnifyingGlassIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');

// Debounced search
let timeout = null;
watch(search, (val) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('marketplace.index'), 
            { search: val, category: selectedCategory.value }, 
            { preserveState: true, preserveScroll: true }
        );
    }, 300);
});

const filterByCategory = (slug) => {
    selectedCategory.value = slug === selectedCategory.value ? '' : slug;
    router.get(route('marketplace.index'), 
        { search: search.value, category: selectedCategory.value }, 
        { preserveState: true, preserveScroll: true }
    );
};

const billingLabel = (cycle) => {
    const map = {
        'monthly': '/mes',
        'annual': '/anual',
        'lifetime': 'Pago Único'
    };
    return map[cycle] || cycle;
};
</script>

<template>
    <Head title="Catálogo de Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Servicios / Marketplace</h2>
        </template>

        <div class="py-0">
            <!-- Full width container as requested -->
            <div class="w-full">
                
                <!-- Filters -->
                <div class="mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <!-- Categories -->
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="filterByCategory('')"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors border"
                            :class="!selectedCategory ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                        >
                            Todos
                        </button>
                        <button 
                            v-for="cat in categories" 
                            :key="cat.id"
                            @click="filterByCategory(cat.slug)"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors border flex items-center gap-2"
                            :class="selectedCategory === cat.slug ? 'bg-brand text-white border-brand' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
                        >
                            <span v-if="cat.icon">{{ cat.icon }}</span>
                            {{ cat.name }}
                        </button>
                    </div>

                    <!-- Search -->
                    <div class="relative w-full md:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
                        </div>
                        <input 
                            v-model="search"
                            type="text"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-brand focus:ring-brand sm:text-sm transition duration-150 ease-in-out"
                            placeholder="Buscar servicios..."
                        />
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="products.data.length === 0" class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-lg">No encontramos productos con esos filtros.</p>
                    <button @click="filterByCategory(''); search=''" class="mt-4 text-brand font-medium hover:underline">Ver todos los productos</button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="product in products.data" :key="product.id" class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex justify-between items-start mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ product.product_category.name }}
                                </span>
                                <!-- Icon/Image Placeholder -->
                                <div class="h-10 w-10 flex items-center justify-center bg-brand/10 rounded-lg text-brand text-xl">
                                    {{ product.product_category.icon || '🚀' }}
                                </div>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ product.name }}</h3>
                            <p class="text-sm text-gray-500 mb-6 line-clamp-2 h-10">{{ product.description }}</p>

                            <!-- Features Preview (First 3) -->
                            <ul v-if="product.features && product.features.length" class="space-y-2 mb-6">
                                <li v-for="(feature, i) in product.features.slice(0, 3)" :key="i" class="flex items-start gap-2 text-sm text-gray-600">
                                    <CheckIcon class="h-5 w-5 text-green-500 flex-shrink-0" />
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 p-6 border-t border-gray-100 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Desde</p>
                                <div class="flex items-baseline gap-1">
                                     <p class="text-lg font-bold text-gray-900">${{ parseFloat(product.base_price).toFixed(2) }}</p>
                                     <span class="text-xs text-gray-500 font-medium">{{ billingLabel(product.billing_cycle) }}</span>
                                </div>
                            </div>
                            <Link 
                                :href="route('marketplace.show', product.slug)" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-colors"
                            >
                                Ver Detalles
                            </Link>
                        </div>
                    </div>
                </div>


                <!-- Pagination -->
                <div v-if="products.links && products.links.length > 3" class="mt-8 flex justify-center">
                    <div class="flex gap-1">
                        <Link
                            v-for="(link, i) in products.links"
                            :key="i"
                            :href="link.url"
                            v-html="link.label"
                            class="px-4 py-2 rounded-md border text-sm font-medium transition-colors"
                            :class="link.active ? 'bg-brand text-white border-brand' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                            :preserve-scroll="true"
                            :only="['products']"
                        />
                    </div>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>
