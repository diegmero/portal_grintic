<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const currentYear = new Date().getFullYear();
</script>

<template>
    <Head title="Bienvenido" />
    
    <div class="min-h-screen bg-gray-50 flex flex-col justify-between selection:bg-brand selection:text-white dark:bg-zinc-900">
        <!-- Hero Section -->
        <main class="flex-grow flex items-center justify-center px-6">
            <div class="text-center max-w-2xl mx-auto">
                <div class="mb-8 flex justify-center">
                   <!-- Logo Placeholder or Icon -->
                   <div class="h-16 w-16 bg-brand rounded-xl flex items-center justify-center shadow-lg transform -rotate-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-white">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                        </svg>
                   </div>
                </div>

                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl dark:text-white">
                    Portal de <span class="text-brand">Servicios</span>
                </h1>
                
                <p class="mt-6 text-xl text-gray-500 max-w-prose mx-auto dark:text-gray-400">
                    Gestiona tus proyectos, servicios y facturación desde un único lugar. 
                    Simple, rápido y seguro.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <template v-if="canLogin">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand hover:bg-brand-dark md:py-4 md:text-lg transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        >
                            Ir al Dashboard
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </Link>

                        <div v-else class="flex flex-col sm:flex-row gap-4 w-full justify-center">
                            <Link
                                :href="route('login')"
                                class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand hover:bg-brand-dark md:py-4 md:text-lg transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                            >
                                Acceso Clientes
                            </Link>
                             <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center justify-center px-8 py-3 border border-gray-300 text-base font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 md:py-4 md:text-lg transition-all"
                            >
                                Registrarse
                            </Link>
                        </div>
                    </template>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-8 bg-white border-t border-gray-100 dark:bg-zinc-900 dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-400">
                    &copy; {{ currentYear }} Grintic Portal. Todos los derechos reservados.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <span class="sr-only">Soporte</span>
                        Soporte
                    </a>
                    <a href="#" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <span class="sr-only">Privacidad</span>
                        Privacidad
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
