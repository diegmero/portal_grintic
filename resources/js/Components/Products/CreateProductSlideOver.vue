<script setup>
import { ref, computed, watch } from 'vue';
import { XMarkIcon, PlusIcon } from '@heroicons/vue/24/outline';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    open: Boolean,
    product: Object,
    categories: Array,
    types: Array,
    billingCycles: Array,
});

const emit = defineEmits(['close']);

const isEditing = computed(() => !!props.product);

const form = useForm({
    name: '',
    slug: '',
    product_category_id: '',
    type: 'subscription',
    billing_cycle: 'monthly',
    base_price: '',
    description: '',
    features: [],
    addons: [],
    is_active: true,
});

// Slugify helper
const slugify = (text) => {
    return text
        .toString()
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')     // Replace spaces with -
        .replace(/[^\w\-]+/g, '') // Remove all non-word chars
        .replace(/\-\-+/g, '-')   // Replace multiple - with single -
        .replace(/^-+/, '')       // Trim - from start of text
        .replace(/-+$/, '');      // Trim - from end of text
};

// Filtered Billing Cycles based on Type
const availableBillingCycles = computed(() => {
    if (form.type === 'one_time') {
        return props.billingCycles.filter(c => c.value === 'lifetime');
    }
    // For subscriptions, exclude lifetime
    return props.billingCycles.filter(c => c.value !== 'lifetime');
});

// Auto-switch billing cycle when type changes
watch(() => form.type, (newType) => {
    if (newType === 'one_time') {
        form.billing_cycle = 'lifetime';
    } else {
        form.billing_cycle = 'monthly';
    }
});

const addAddon = () => {
    form.addons.push({
        name: '',
        additional_price: 0,
        is_active: true
    });
};

const removeAddon = (index) => {
    form.addons.splice(index, 1);
};

const addFeature = () => {
    form.features.push('');
};

const removeFeature = (index) => {
    form.features.splice(index, 1);
};

// Auto-generate slug from name
watch(() => form.name, (val) => {
    // Only auto-generate if creating or if slug matches generated name
    if (!props.product || !form.slug || form.slug === slugify(props.product.name)) {
         form.slug = slugify(val);
    }
});

watch(() => props.open, (open) => {
    if (open) {
        // Deferred loading to allow animation to start
        setTimeout(() => {
            if (props.product) {
                form.name = props.product.name;
                form.slug = props.product.slug;
                // Use product_category_id from prop, fallback to first category if null (migration safe)
                form.product_category_id = props.product.product_category_id || (props.categories.length > 0 ? props.categories[0].value : '');
                form.type = props.product.type;
                form.billing_cycle = props.product.billing_cycle || 'monthly';
                form.base_price = props.product.base_price;
                form.description = props.product.description || '';
                form.features = props.product.features || [];
                form.is_active = props.product.is_active;
                
                // Addons
                form.addons = props.product.addons ? props.product.addons.map(v => ({
                    id: v.id,
                    name: v.name,
                    additional_price: v.additional_price,
                    is_active: !!v.is_active
                })) : [];
            } else {
                form.reset();
                // Default to first category
                form.product_category_id = props.categories.length > 0 ? props.categories[0].value : '';
                form.type = 'subscription';
                form.billing_cycle = 'monthly';
                form.is_active = true;
                form.addons = [];
                form.features = [];
            }
        }, 100);

    } else {
        // Deferred clearing on close
        setTimeout(() => {
           form.reset(); 
        }, 500);
    }
});

const submit = () => {
    if (isEditing.value) {
        form.put(route('products.update', props.product.id), {
            onSuccess: () => {
                emit('close');
            },
        });
    } else {
        form.post(route('products.store'), {
            onSuccess: () => {
                emit('close');
            },
        });
    }
};
</script>

<template>
    <div class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <Transition
            enter-active-class="ease-in-out duration-500"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in-out duration-500"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="open" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="emit('close')"></div>
        </Transition>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <Transition
                        enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        enter-from-class="translate-x-full"
                        enter-to-class="translate-x-0"
                        leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                        leave-from-class="translate-x-0"
                        leave-to-class="translate-x-full"
                    >
                        <div v-show="open" class="pointer-events-auto w-screen max-w-md">
                            <form @submit.prevent="submit" class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <div class="bg-night px-4 py-6 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            {{ isEditing ? 'Editar Producto' : 'Nuevo Producto' }}
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" class="relative rounded-md bg-night text-gray-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="emit('close')">
                                                <span class="absolute -inset-2.5" />
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <p class="text-sm text-gray-300">Configura los detalles del producto.</p>
                                    </div>
                                </div>
                                <div class="relative flex-1 px-4 py-6 sm:px-6 space-y-6">
                                    
                                    <div>
                                        <InputLabel for="name" value="Nombre del Producto" />
                                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required placeholder="Ej: Hosting Básico" />
                                        <InputError :message="form.errors.name" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="slug" value="Slug (Opcional)" />
                                        <TextInput id="slug" v-model="form.slug" type="text" class="mt-1 block w-full" placeholder="Ej: hosting-basico" />
                                        <p class="text-xs text-gray-500 mt-1">Se generará automáticamente si se deja vacío.</p>
                                        <InputError :message="form.errors.slug" class="mt-2" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="category" value="Categoría" />
                                            <CustomSelect
                                                v-model="form.product_category_id"
                                                :options="categories"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.product_category_id" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="type" value="Tipo" />
                                            <CustomSelect
                                                v-model="form.type"
                                                :options="types"
                                                class="mt-1"
                                            />
                                            <InputError :message="form.errors.type" class="mt-2" />
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="billing_cycle" value="Ciclo de Facturación" />
                                            <CustomSelect
                                                v-model="form.billing_cycle"
                                                :options="availableBillingCycles"
                                                class="mt-1"
                                                :disabled="form.type === 'one_time'"
                                            />
                                            <InputError :message="form.errors.billing_cycle" class="mt-2" />
                                        </div>

                                        <div>
                                            <InputLabel for="base_price" value="Precio Base (USD)" />
                                            <input 
                                                id="base_price" 
                                                v-model="form.base_price" 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                class="mt-1 block w-full rounded-lg bg-white py-2 pl-3 pr-3 text-sm shadow-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand"
                                                required 
                                                placeholder="0.00" 
                                            />
                                            <InputError :message="form.errors.base_price" class="mt-2" />
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel for="description" value="Descripción (Opcional)" />
                                        <textarea 
                                            id="description" 
                                            v-model="form.description" 
                                            rows="3" 
                                            class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm"
                                            placeholder="Detalles del producto..."
                                        ></textarea>
                                        <InputError :message="form.errors.description" class="mt-2" />
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input 
                                            type="checkbox" 
                                            id="is_active" 
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-brand focus:ring-brand"
                                        />
                                        <InputLabel for="is_active" value="Producto Activo" class="!mb-0" />
                                    </div>

                                    <div class="pt-6 border-t border-gray-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-sm font-medium text-gray-900">Características (Incluido)</h3>
                                            <button type="button" @click="addFeature" class="text-xs text-brand hover:text-brand-dark font-medium flex items-center gap-1">
                                                <PlusIcon class="h-4 w-4" />
                                                Agregar
                                            </button>
                                        </div>

                                        <div v-if="form.features.length === 0" class="text-center py-4 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                            <p class="text-xs text-gray-500">Agrega características (ej. 2GB RAM, SSL Gratis)</p>
                                        </div>

                                        <div v-else class="space-y-2">
                                            <div v-for="(feature, index) in form.features" :key="index" class="flex items-center gap-2">
                                                <TextInput v-model="form.features[index]" type="text" class="block w-full !py-1 !px-2 !text-sm" placeholder="Ej. 2GB RAM" />
                                                <button type="button" @click="removeFeature(index)" class="text-gray-400 hover:text-red-500">
                                                    <XMarkIcon class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-6 border-t border-gray-200">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-sm font-medium text-gray-900">Servicios Adicionales (Extras)</h3>
                                            <button type="button" @click="addAddon" class="text-xs text-brand hover:text-brand-dark font-medium flex items-center gap-1">
                                                <PlusIcon class="h-4 w-4" />
                                                Agregar
                                            </button>
                                        </div>

                                        <div v-if="form.addons.length === 0" class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                                            <p class="text-xs text-gray-500">No hay adicionales configurados (ej. IP Extra).</p>
                                        </div>

                                        <div v-else class="space-y-4">
                                            <div v-for="(addon, index) in form.addons" :key="index" class="bg-gray-50 p-3 rounded-lg border border-gray-200 relative group">
                                                <button type="button" @click="removeAddon(index)" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
                                                    <XMarkIcon class="h-4 w-4" />
                                                </button>
                                                
                                                <div class="grid grid-cols-6 gap-3">
                                                    <div class="col-span-4">
                                                        <InputLabel :for="'addon_name_'+index" value="Nombre" class="text-xs" />
                                                        <TextInput :id="'addon_name_'+index" v-model="addon.name" type="text" class="mt-1 block w-full !py-1 !px-2 !text-sm" placeholder="Ej. IP Adicional" />
                                                        <InputError :message="form.errors[`addons.${index}.name`]" class="mt-1" />
                                                    </div>
                                                    <div class="col-span-2">
                                                        <InputLabel :for="'addon_price_'+index" value="+ Precio" class="text-xs" />
                                                        <input 
                                                            :id="'addon_price_'+index"
                                                            v-model="addon.additional_price"
                                                            type="number"
                                                            step="0.01"
                                                            min="0"
                                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand focus:ring-brand sm:text-sm py-1 px-2"
                                                        />
                                                        <InputError :message="form.errors[`addons.${index}.additional_price`]" class="mt-1" />
                                                    </div>
                                                </div>
                                                <div class="mt-2 flex items-center gap-2">
                                                    <input 
                                                        type="checkbox" 
                                                        :id="'addon_active_'+index" 
                                                        v-model="addon.is_active"
                                                        class="h-4 w-4 rounded border-gray-300 text-brand focus:ring-brand"
                                                    />
                                                    <label :for="'addon_active_'+index" class="text-xs text-gray-600">Activo</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="flex flex-shrink-0 justify-end px-4 py-4 gap-3 bg-gray-50 border-t border-gray-200">
                                    <SecondaryButton @click="emit('close')">Cancelar</SecondaryButton>
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                        {{ isEditing ? 'Actualizar' : 'Crear Producto' }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </div>
</template>
