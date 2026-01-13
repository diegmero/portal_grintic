<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PencilSquareIcon, TrashIcon, PlusIcon, FolderIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Object,
});

const showingModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    slug: '',
    icon: '',
    description: '',
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

watch(() => form.name, (val) => {
    // Only auto-generate if we are creating a new category OR if the slug is empty/matches the old name
    // Simple UX: Always update slug if it looks like a generated slug of the name
    if (!editingCategory.value || !form.slug || form.slug === slugify(editingCategory.value.name)) {
         form.slug = slugify(val);
    }
});

const openCreateModal = () => {
    editingCategory.value = null;
    form.reset();
    showingModal.value = true;
};

const openEditModal = (category) => {
    editingCategory.value = category;
    form.reset();
    form.name = category.name;
    form.slug = category.slug;
    form.icon = category.icon;
    form.description = category.description;
    showingModal.value = true;
};

const closeModal = () => {
    showingModal.value = false;
    form.reset();
    editingCategory.value = null;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('product-categories.update', editingCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('product-categories.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCategory = (category) => {
    if (confirm(`¿Estás seguro de eliminar la categoría "${category.name}"?`)) {
        useForm({}).delete(route('product-categories.destroy', category.id));
    }
};
</script>

<template>
    <Head title="Categorías de Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-gray-900">Categorías de Productos</h2>
                    <p class="text-sm text-gray-500 mt-1">Gestiona las categorías para organizar el catálogo.</p>
                </div>
                <PrimaryButton @click="openCreateModal" class="inline-flex items-center gap-2">
                    <PlusIcon class="h-5 w-5" />
                    Nueva Categoría
                </PrimaryButton>
            </div>
        </template>

        <div class="py-0">
            <div class="w-full mx-auto">
                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-xl overflow-hidden">
                    <div class="p-0">
                        
                        <!-- Empty State -->
                        <div v-if="categories.data.length === 0" class="text-center py-12">
                            <FolderIcon class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                            <h3 class="text-lg font-medium text-gray-900">No hay categorías</h3>
                            <p class="text-gray-500 mt-1">Crea la primera categoría para organizar tus productos.</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <span v-if="category.icon" class="mr-3 text-xl">{{ category.icon }}</span>
                                                <div class="font-medium text-gray-900">{{ category.name }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-medium rounded-full bg-gray-100 text-gray-700">
                                                {{ category.slug }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-600 truncate max-w-xs">{{ category.description || '—' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center rounded-md bg-brand/10 px-2 py-1 text-xs font-medium text-brand ring-1 ring-inset ring-brand/20">{{ category.products_count }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEditModal(category)" class="text-gray-400 hover:text-brand transition-colors">
                                                    <PencilSquareIcon class="h-5 w-5" />
                                                </button>
                                                <button @click="deleteCategory(category)" class="text-gray-400 hover:text-red-500 transition-colors" :disabled="category.products_count > 0" :class="{'opacity-25 cursor-not-allowed': category.products_count > 0}">
                                                    <TrashIcon class="h-5 w-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="categories.links && categories.links.length > 3" class="border-t border-gray-200 px-4 py-3 sm:px-6">
                            <div class="flex justify-center">
                                <div class="flex gap-1">
                                    <Link
                                        v-for="(link, i) in categories.links"
                                        :key="i"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 rounded-md"
                                        :class="{ 'bg-brand text-white ring-brand hover:bg-brand-dark focus-visible:outline-brand': link.active, 'opacity-50 cursor-not-allowed': !link.url }"
                                        :preserve-scroll="true"
                                    />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <Modal :show="showingModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
                </h2>

                <div class="mt-6 space-y-6">
                    <div>
                        <InputLabel for="name" value="Nombre" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej. Hosting Premium"
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="slug" value="Slug (Opcional)" />
                        <TextInput
                            id="slug"
                            v-model="form.slug"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej. hosting-premium"
                        />
                        <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, se generará automáticamente.</p>
                        <InputError :message="form.errors.slug" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="icon" value="Icono/Emoji" />
                        <TextInput
                            id="icon"
                            v-model="form.icon"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej. 🚀"
                        />
                        <InputError :message="form.errors.icon" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Descripción" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="mt-1 block w-full border-gray-300 focus:border-brand focus:ring-brand rounded-md shadow-sm"
                            rows="3"
                        ></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Cancelar </SecondaryButton>
                    <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="submit">
                        {{ editingCategory ? 'Actualizar' : 'Crear' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
