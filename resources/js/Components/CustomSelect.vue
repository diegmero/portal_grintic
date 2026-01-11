<script setup>
import { computed } from 'vue';
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        required: true,
        // Each option: { value: string|number, label: string }
    },
    placeholder: {
        type: String,
        default: 'Seleccionar...',
    },
});

const emit = defineEmits(['update:modelValue']);

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue) || null;
});

const displayLabel = computed(() => {
    return selectedOption.value?.label || props.placeholder;
});

const updateValue = (option) => {
    emit('update:modelValue', option.value);
};
</script>

<template>
    <Listbox :model-value="selectedOption" @update:model-value="updateValue">
        <div class="relative">
            <ListboxButton class="relative w-full cursor-pointer rounded-lg bg-white py-2 pl-3 pr-10 text-left text-sm shadow-sm ring-1 ring-inset ring-gray-300 focus:outline-none focus:ring-2 focus:ring-brand">
                <span class="block truncate" :class="{ 'text-gray-500': !selectedOption, 'text-gray-900': selectedOption }">
                    {{ displayLabel }}
                </span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                </span>
            </ListboxButton>

            <transition
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <ListboxOptions class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white py-1 text-sm shadow-lg ring-1 ring-black/5 focus:outline-none">
                    <ListboxOption
                        v-for="option in options"
                        :key="option.value"
                        :value="option"
                        v-slot="{ active, selected }"
                        as="template"
                    >
                        <li
                            :class="[
                                active ? 'bg-brand/10 text-brand' : 'text-gray-900',
                                'relative cursor-pointer select-none py-2 pl-10 pr-4',
                            ]"
                        >
                            <span :class="[selected ? 'font-semibold' : 'font-normal', 'block truncate']">
                                {{ option.label }}
                            </span>
                            <span
                                v-if="selected"
                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-brand"
                            >
                                <CheckIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>
