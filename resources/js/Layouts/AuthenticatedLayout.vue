<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CommandPalette from '@/Components/CommandPalette.vue';
import { Bars3Icon, BellIcon, MagnifyingGlassIcon, XMarkIcon, HomeIcon, UsersIcon, FolderIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline';

const showingNavigationDropdown = ref(false);
const showCommandPalette = ref(false);

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isClient = computed(() => {
    return user.value?.roles?.some(role => role.name === 'client');
});

const navigation = computed(() => {
    const nav = [
        { name: isClient.value ? 'Mi Portal' : 'Dashboard', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') },
    ];

    if (!isClient.value) {
        nav.push({ name: 'Clientes', href: route('clients.index'), icon: UsersIcon, current: route().current('clients.*') });
    }

    nav.push({ name: isClient.value ? 'Mis Proyectos' : 'Proyectos', href: route('projects.index'), icon: FolderIcon, current: route().current('projects.*') });
    nav.push({ name: isClient.value ? 'Mis Finanzas' : 'Finanzas', href: route('finance.index'), icon: CurrencyDollarIcon, current: route().current('finance.*') });

    return nav;
});

import ToastNotification from '@/Components/ToastNotification.vue';

const openPalette = () => {
    showCommandPalette.value = true;
};

const closePalette = () => {
    showCommandPalette.value = false;
};

const onKeydown = (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        openPalette();
    }
};

onMounted(() => {
    window.addEventListener('keydown', onKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeydown);
});

</script>

<template>
    <div class="min-h-screen bg-pastel text-gray-900 font-sans">
        
        <!-- Command Palette -->
        <CommandPalette :open="showCommandPalette" @close="closePalette" />
        <ToastNotification />

        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-night text-white shadow-xl hidden md:flex flex-col transition-all duration-300">
            <div class="flex h-16 shrink-0 items-center px-6 bg-night border-b border-white/10">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-auto fill-current text-white" />
                    <span class="font-bold text-lg tracking-wide">GrinWeb</span>
                </Link>
            </div>
            <nav class="flex flex-1 flex-col px-4 py-6 space-y-1">
                <Link v-for="item in navigation" :key="item.name" :href="item.href" :class="[item.current ? 'bg-brand text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white', 'group flex gap-x-3 rounded-xl p-3 text-sm font-semibold leading-6 transition-colors']">
                    <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                    {{ item.name }}
                </Link>
            </nav>
            <div class="p-4 border-t border-white/10">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-brand flex items-center justify-center text-white font-bold">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                    <div class="flex flex-col">
                         <span class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</span>
                         <span class="text-xs text-gray-400 capitalize">{{ $page.props.auth.user.roles[0]?.name || 'User' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="md:pl-64 flex flex-col min-h-screen transition-all duration-300">
            
            <!-- Top Header -->
            <header class="sticky top-0 z-40 flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 md:hidden" @click="showingNavigationDropdown = true">
                    <span class="sr-only">Open sidebar</span>
                    <Bars3Icon class="h-6 w-6" aria-hidden="true" />
                </button>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="relative flex flex-1 items-center" @click="openPalette">
                         <div class="w-full max-w-md relative text-gray-400 focus-within:text-gray-600 cursor-pointer">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <MagnifyingGlassIcon class="h-5 w-5" aria-hidden="true" />
                            </div>
                            <div class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6 bg-gray-50/50">
                                Buscar (Cmd+K)
                            </div>
                         </div>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">View notifications</span>
                            <BellIcon class="h-6 w-6" aria-hidden="true" />
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="-m-1.5 flex items-center p-1.5">
                                        <span class="sr-only">Open user menu</span>
                                        <img class="h-8 w-8 rounded-full bg-gray-50" src="https://ui-avatars.com/api/?name=Admin+User&background=random" alt="" />
                                        <span class="hidden lg:flex lg:items-center">
                                            <span class="ml-4 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">{{ $page.props.auth.user.name }}</span>
                                        </span>
                                    </button>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button"> Log Out </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-10">
                <div class="px-4 sm:px-6 lg:px-8">
                    <!-- Page Header -->
                    <div v-if="$slots.header" class="mb-8 flex items-center justify-between">
                         <slot name="header" />
                    </div>

                     <!-- Slot Content -->
                     <slot />
                </div>
            </main>
        </div>
    </div>
</template>
