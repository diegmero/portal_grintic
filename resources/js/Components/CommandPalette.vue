<script setup>
import {
  Combobox,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
  Dialog,
  DialogPanel,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/20/solid'
import { ExclamationTriangleIcon, FolderIcon, LifebuoyIcon, ShareIcon, UsersIcon } from '@heroicons/vue/24/outline'
import { usePage, router } from '@inertiajs/vue3'
import { computed, ref, defineEmits, defineProps, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  open: Boolean,
})

const emit = defineEmits(['close'])
const query = ref('')

const page = usePage()
const isClient = computed(() => {
    return page.props.auth.user?.roles?.some(role => role.name === 'client')
})

const links = computed(() => {
  const base = [
    { name: isClient.value ? 'Mi Portal' : 'Dashboard', href: '/dashboard', icon: HomeIcon },
    { name: isClient.value ? 'Mis Proyectos' : 'Proyectos', href: '/projects', icon: FolderIcon },
    { name: isClient.value ? 'Mis Finanzas' : 'Finanzas', href: '/finance', icon: CurrencyDollarIcon },
  ];

  if (!isClient.value) {
    base.push({ name: 'Clientes', href: '/clients', icon: UsersIcon });
    base.push({ name: 'Crear Nuevo Cliente', href: '/clients?create=true', icon: UsersIcon });
  }

  return base;
})

import { HomeIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline'

const filteredItems = computed(() => {
  return query.value === ''
    ? links.value
    : links.value.filter((item) => {
        return item.name.toLowerCase().includes(query.value.toLowerCase())
      })
})

const onSelect = (item) => {
  if (item.href) {
    router.visit(item.href)
  }
  emit('close')
}
</script>

<template>
  <TransitionRoot :show="open" as="template" @after-leave="query = ''" appear>
    <Dialog as="div" class="relative z-50" @close="emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500/25 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 md:p-20">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="mx-auto max-w-xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5 transition-all"
          >
            <Combobox @update:modelValue="onSelect">
              <div class="relative">
                <MagnifyingGlassIcon
                  class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400"
                  aria-hidden="true"
                />
                <ComboboxInput
                  class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                  placeholder="Search..."
                  @change="query = $event.target.value"
                />
              </div>

              <ComboboxOptions
                v-if="filteredItems.length > 0"
                static
                class="max-h-96 scroll-py-3 overflow-y-auto p-3"
              >
                <ComboboxOption
                  v-for="item in filteredItems"
                  :key="item.name"
                  :value="item"
                  as="template"
                  v-slot="{ active }"
                >
                  <li
                    :class="[
                      'flex cursor-default select-none rounded-xl p-3',
                      active ? 'bg-gray-100' : '',
                    ]"
                  >
                    <div
                      :class="['flex h-10 w-10 flex-none items-center justify-center rounded-lg', active ? 'bg-white' : 'bg-gray-100']"
                    >
                      <component :is="item.icon" class="h-6 w-6 text-gray-500" aria-hidden="true" />
                    </div>
                    <div class="ml-4 flex-auto">
                      <p :class="['text-sm font-medium', active ? 'text-gray-900' : 'text-gray-700']">
                        {{ item.name }}
                      </p>
                      <p :class="['text-sm', active ? 'text-gray-700' : 'text-gray-500']">
                        Jump to {{ item.name }}
                      </p>
                    </div>
                  </li>
                </ComboboxOption>
              </ComboboxOptions>

              <div v-if="query !== '' && filteredItems.length === 0" class="px-6 py-14 text-center sm:px-14">
                <ExclamationTriangleIcon class="mx-auto h-6 w-6 text-gray-400" aria-hidden="true" />
                <p class="mt-4 font-semibold text-gray-900">No results found</p>
                <p class="mt-2 text-gray-500">No components found for this search term. Please try again.</p>
              </div>
            </Combobox>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
