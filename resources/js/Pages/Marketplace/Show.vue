<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { CheckIcon, ArrowLeftIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    product: Object,
});

// Selected Addon IDs
const selectedAddons = ref([]);

// Toggle addon selection
const toggleAddon = (addonId) => {
    const index = selectedAddons.value.indexOf(addonId);
    if (index === -1) {
        selectedAddons.value.push(addonId);
    } else {
        selectedAddons.value.splice(index, 1);
    }
};

// Calculate Total Price
const totalPrice = computed(() => {
    let total = parseFloat(props.product.base_price);
    
    selectedAddons.value.forEach(addonId => {
        const addon = props.product.addons.find(a => a.id === addonId);
        if (addon) {
            total += parseFloat(addon.additional_price);
        }
    });

    return total.toFixed(2);
});

// Submit Form
const form = useForm({
    configuration: [],
    total_price: 0,
});

// Helper for billing cycle label
const billingLabel = (cycle) => {
    const map = {
        'monthly': '/mes',
        'annual': '/año',
        'lifetime': 'Pago Único'
    };
    return map[cycle] || cycle;
};

const submit = () => {
    // Build configuration array for backend
    const config = selectedAddons.value.map(addonId => {
        const addon = props.product.addons.find(a => a.id === addonId);
        return {
            id: addon.id,
            name: addon.name,
            price: addon.additional_price
        };
    });

    form.configuration = config;
    form.total_price = totalPrice.value;

    form.post(route('marketplace.request', props.product.id), {
        onFinish: () => {
            // Optional: reset or redirect handled by backend
        }
    });
};
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <template #header>
             <div class="flex items-center gap-4">
                <Link :href="route('marketplace.index')" class="text-gray-400 hover:text-gray-600">
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <div class="flex items-center gap-2">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ product.name }}</h2>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                        {{ billingLabel(product.billing_cycle) }}
                    </span>
                </div>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                    
                    <!-- Left Column: Details -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Description & Features -->
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                             <div class="flex items-center gap-3 mb-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand">
                                    {{ product.product_category.name }}
                                </span>
                             </div>

                             <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ product.name }}</h1>
                             <p class="text-gray-600 text-lg mb-8 leading-relaxed">{{ product.description }}</p>

                             <h3 class="text-lg font-semibold text-gray-900 mb-4">Que incluye:</h3>
                             <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <li v-for="(feature, i) in product.features" :key="i" class="flex items-start gap-3 text-gray-700">
                                    <div class="mt-0.5 rounded-full bg-green-100 p-1">
                                        <CheckIcon class="h-4 w-4 text-green-600" />
                                    </div>
                                    <span>{{ feature }}</span>
                                </li>
                             </ul>
                        </div>

                        <!-- Addons Selection -->
                        <div v-if="product.addons && product.addons.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6">Personaliza tu servicio (Extras)</h3>
                            <div class="space-y-4">
                                <div 
                                    v-for="addon in product.addons" 
                                    :key="addon.id"
                                    class="relative flex items-start p-4 border rounded-xl hover:bg-gray-50 transition-colors cursor-pointer"
                                    :class="selectedAddons.includes(addon.id) ? 'border-brand bg-brand/5 ring-1 ring-brand' : 'border-gray-200'"
                                    @click="toggleAddon(addon.id)"
                                >
                                    <div class="flex h-6 items-center">
                                        <input 
                                            type="checkbox" 
                                            :checked="selectedAddons.includes(addon.id)"
                                            class="h-4 w-4 rounded border-gray-300 text-brand focus:ring-brand"
                                        />
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <label class="font-medium text-gray-900 cursor-pointer">{{ addon.name }}</label>
                                    </div>
                                    <div class="ml-3 font-semibold text-gray-900">
                                        +${{ parseFloat(addon.additional_price).toFixed(2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sticky Summary -->
                    <div class="mt-8 lg:mt-0">
                        <div class="sticky top-6 bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-6">Resumen del Pedido</h3>

                            <div class="flow-root">
                                <dl class="-my-4 divide-y divide-gray-200 text-sm">
                                    <div class="flex items-center justify-between py-4">
                                        <dt class="text-gray-600">Precio Base</dt>
                                        <dd class="font-medium text-gray-900">${{ parseFloat(product.base_price).toFixed(2) }}</dd>
                                    </div>
                                    <div v-for="addonId in selectedAddons" :key="addonId" class="flex items-center justify-between py-4 bg-gray-50/50 px-2 rounded-md -mx-2">
                                        <dt class="text-gray-600 truncate max-w-[150px]">{{ product.addons.find(a => a.id === addonId).name }}</dt>
                                        <dd class="font-medium text-gray-900">+${{ parseFloat(product.addons.find(a => a.id === addonId).additional_price).toFixed(2) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between py-4 border-t-2 border-gray-100">
                                        <dt class="text-base font-bold text-gray-900">Total a Pagar</dt>
                                        <div class="text-right">
                                             <dd class="text-2xl font-bold text-brand">${{ totalPrice }}</dd>
                                             <p class="text-xs text-gray-500 font-medium">{{ billingLabel(product.billing_cycle) }}</p>
                                        </div>
                                    </div>
                                </dl>
                            </div>

                            <div class="mt-6 flex flex-col gap-3">
                                <button 
                                    @click="submit" 
                                    :disabled="form.processing"
                                    class="w-full flex justify-center items-center px-4 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-brand hover:bg-brand-dark shadow-sm hover:shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand"
                                    :class="{'opacity-75 cursor-wait': form.processing}"
                                >
                                    <ShoppingCartIcon class="h-5 w-5 mr-2" />
                                    {{ form.processing ? 'Procesando...' : 'Solicitar Servicio' }}
                                </button>
                                <p class="text-center text-xs text-gray-500">
                                    * La activación puede tomar hasta 24 horas.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
