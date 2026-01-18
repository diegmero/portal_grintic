<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    admin: Object, // Null if creating
});

const isEditing = !!props.admin;

const form = useForm({
    name: props.admin?.name || '',
    email: props.admin?.email || '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    if (isEditing) {
        form.put(route('admins.update', props.admin.id), {
            onSuccess: () => form.reset('password', 'password_confirmation'),
        });
    } else {
        form.post(route('admins.store'), {
            onSuccess: () => form.reset(),
        });
    }
};
</script>

<template>
    <Head :title="isEditing ? 'Editar Administrador' : 'Nuevo Administrador'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ isEditing ? 'Editar Administrador' : 'Nuevo Administrador' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-8 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Nombre Completo" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div>
                            <InputLabel for="email" value="Correo Electrónico" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div>
                            <InputLabel for="password" :value="isEditing ? 'Nueva Contraseña (Opcional)' : 'Contraseña'" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                :required="!isEditing"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <!-- Password Confirmation -->
                        <div>
                            <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password_confirmation"
                                :required="!isEditing"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-4 border-t">
                            <Link :href="route('admins.index')" class="text-sm text-gray-600 hover:text-gray-900">
                                Cancelar
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                {{ isEditing ? 'Guardar Cambios' : 'Crear Administrador' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
