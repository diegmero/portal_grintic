<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    status: Number,
});

const title = computed(() => {
    return {
        503: 'Servicio No Disponible',
        500: 'Error del Servidor',
        404: 'Página No Encontrada',
        403: '¿Estás perdido?',
    }[props.status];
});

const description = computed(() => {
    return {
        503: 'Lo sentimos, estamos realizando mantenimiento. Por favor, intenta de nuevo más tarde.',
        500: '¡Vaya! Algo salió mal en nuestros servidores.',
        404: 'Lo sentimos, la página que buscas no existe.',
        403: 'No tienes permisos para acceder a esta sección. Si crees que es un error, contacta al administrador.',
    }[props.status];
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 p-4 text-center">
        <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full ring-1 ring-gray-900/5">
            <h1 class="text-6xl font-black text-brand mb-4">{{ status }}</h1>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ title }}</h2>
            <p class="text-gray-500 mb-8">{{ description }}</p>
            
            <Link :href="route('dashboard')">
                <PrimaryButton>
                    Volver al Inicio
                </PrimaryButton>
            </Link>
        </div>
    </div>
</template>
