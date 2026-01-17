<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import CommandPalette from '@/Components/CommandPalette.vue';
import { Bars3Icon, MagnifyingGlassIcon, XMarkIcon, HomeIcon, UsersIcon, FolderIcon, CurrencyDollarIcon, CubeIcon, TagIcon, ShoppingBagIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';
import NotificationDropdown from '@/Components/NotificationDropdown.vue';

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
        const groups = [];

        // 1. Dashboard (No Group)
        groups.push({
            title: null,
            items: [
                { name: isClient.value ? 'Mi Portal' : 'Dashboard', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') }
            ]
        });

        // 2. CRM (Admin Only)
        if (!isClient.value) {
            groups.push({
                title: 'CRM',
                items: [
                    { name: 'Clientes', href: route('clients.index'), icon: UsersIcon, current: route().current('clients.*') }
                ]
            });
        }

        // 3. Operaciones (Core Business)
        const opsItems = [];
        
        // Projects
        opsItems.push({ 
            name: isClient.value ? 'Mis Proyectos' : 'Proyectos', 
            href: isClient.value ? route('portal.projects') : route('projects.index'), 
            icon: FolderIcon, 
            current: isClient.value ? route().current('portal.projects*') : route().current('projects.*') 
        });


        // Requests
        if (isClient.value) {
            opsItems.push({ name: 'Mis Solicitudes', href: route('portal.requests.index'), icon: ClipboardDocumentListIcon, current: route().current('portal.requests.*') });
            opsItems.push({ name: 'Mis Servicios', href: route('portal.services'), icon: CubeIcon, current: route().current('portal.services') });
        } else {
            opsItems.push({ name: 'Solicitudes', href: route('admin.requests.index'), icon: ClipboardDocumentListIcon, current: route().current('admin.requests.*') });
        }

        groups.push({
            title: 'OPERACIONES',
            items: opsItems
        });

        // 4. Catálogo / Inventario (Admin Only) or Marketplace (Client)
        if (!isClient.value) {
            groups.push({
                title: 'CATÁLOGO',
                items: [
                    { name: 'Productos', href: route('products.index'), icon: CubeIcon, current: route().current('products.*') || route().current('services.*') },
                    { name: 'Categorías', href: route('product-categories.index'), icon: TagIcon, current: route().current('product-categories.*') },
                ]
            });
        } else {
            groups.push({
                title: 'CONTRATACIÓN',
                items: [
                    { name: 'Catálogo / Marketplace', href: route('marketplace.index'), icon: ShoppingBagIcon, current: route().current('marketplace.*') }
                ]
            });
        }

        // 5. Finanzas
        groups.push({
            title: 'FINANZAS',
            items: [
                { 
                    name: isClient.value ? 'Mis Finanzas' : 'Finanzas', 
                    href: isClient.value ? route('portal.invoices') : route('invoices.index'), 
                    icon: CurrencyDollarIcon, 
                    current: isClient.value ? route().current('portal.invoices*') : route().current('invoices.*') 
                }
            ]
        });

        return groups;
    });

// ... imports remain the same

</script>

<template>
    <div class="min-h-screen bg-pastel text-gray-900 font-sans">
        
        <!-- Command Palette -->
        <CommandPalette :open="showCommandPalette" @close="closePalette" />
        <ToastNotification />

        <!-- Mobile Sidebar -->
        <div v-show="showingNavigationDropdown" class="relative z-50 md:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/80 transition-opacity ease-linear duration-300" @click="showingNavigationDropdown = false"></div>

            <div class="fixed inset-0 flex">
                <!-- Off-canvas menu -->
                <div class="relative mr-16 flex w-full max-w-xs flex-1 transform transition ease-in-out duration-300">
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5 text-white" @click="showingNavigationDropdown = false">
                            <span class="sr-only">Close sidebar</span>
                            <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                        </button>
                    </div>

                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-night px-6 pb-4 ring-1 ring-white/10">
                        <div class="flex h-16 shrink-0 items-center border-b border-white/10">
                            <ApplicationLogo variant="light" class="h-8 w-auto" />
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                <li>
                                    <div v-for="(group, groupIdx) in navigation" :key="groupIdx" class="mb-6">
                                        <div v-if="group.title" class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                            {{ group.title }}
                                        </div>
                                        <ul role="list" class="space-y-1">
                                            <li v-for="item in group.items" :key="item.name">
                                                <Link 
                                                    :href="item.href" 
                                                    :class="[item.current ? 'bg-brand text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white', 'group flex gap-x-3 rounded-xl p-3 text-sm font-semibold leading-6 transition-colors']"
                                                    @click="showingNavigationDropdown = false"
                                                >
                                                    <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                                    {{ item.name }}
                                                </Link>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="mt-auto">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-brand flex items-center justify-center text-white font-bold">
                                            {{ $page.props.auth.user.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                             <span class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</span>
                                             <span class="text-xs text-gray-400 capitalize">{{ $page.props.auth.user.roles[0]?.name || 'User' }}</span>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (Desktop) -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-night text-white shadow-xl hidden md:flex flex-col transition-all duration-300 no-print">
            <div class="flex h-16 shrink-0 items-center px-6 bg-night border-b border-white/10">
                <Link :href="route('dashboard')" class="flex items-center gap-3">
                    <ApplicationLogo variant="light" class="h-8" />
                </Link>
            </div>
            
            <nav class="flex flex-1 flex-col px-4 py-6 overflow-y-auto">
                <div v-for="(group, groupIdx) in navigation" :key="groupIdx" class="mb-6">
                    <div v-if="group.title" class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {{ group.title }}
                    </div>
                    <ul role="list" class="space-y-1">
                        <li v-for="item in group.items" :key="item.name">
                            <Link :href="item.href" :class="[item.current ? 'bg-brand text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white', 'group flex gap-x-3 rounded-xl p-3 text-sm font-semibold leading-6 transition-colors']">
                                <component :is="item.icon" class="h-6 w-6 shrink-0" aria-hidden="true" />
                                {{ item.name }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="p-4 border-t border-white/10 mt-auto">
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
            <header class="sticky top-0 z-40 flex h-16 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 no-print">
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
                        <NotificationDropdown />

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
                    <div v-if="$slots.header" class="mb-8 flex items-center justify-between no-print">
                         <slot name="header" />
                    </div>

                     <!-- Slot Content -->
                     <slot />
                </div>
            </main>
        </div>
    </div>
</template>
