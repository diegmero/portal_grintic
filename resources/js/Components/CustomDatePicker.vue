<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { CalendarDaysIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
        default: 'Seleccionar fecha...',
    },
    required: Boolean,
});

const emit = defineEmits(['update:modelValue']);

// Unique ID for this instance
const instanceId = ref(Math.random().toString(36).substr(2, 9));

const currentMonth = ref(new Date());
const isOpen = ref(false);
const buttonRef = ref(null);
const panelRef = ref(null);
const panelStyle = ref({});

// Initialize currentMonth from modelValue or today
watch(() => props.modelValue, (val) => {
    if (val) {
        currentMonth.value = new Date(val + 'T00:00:00');
    }
}, { immediate: true });

const monthNames = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const dayNames = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];

const displayValue = computed(() => {
    if (!props.modelValue) return '';
    const date = new Date(props.modelValue + 'T00:00:00');
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
});

const currentMonthYear = computed(() => {
    return `${monthNames[currentMonth.value.getMonth()]} ${currentMonth.value.getFullYear()}`;
});

const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    let startPadding = firstDay.getDay() - 1;
    if (startPadding < 0) startPadding = 6;
    
    const days = [];
    
    const prevMonth = new Date(year, month, 0);
    for (let i = startPadding - 1; i >= 0; i--) {
        days.push({
            day: prevMonth.getDate() - i,
            date: null,
            isCurrentMonth: false,
        });
    }
    
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        days.push({
            day: i,
            date: dateStr,
            isCurrentMonth: true,
            isSelected: props.modelValue === dateStr,
            isToday: dateStr === new Date().toISOString().split('T')[0],
        });
    }
    
    const remaining = 42 - days.length;
    for (let i = 1; i <= remaining; i++) {
        days.push({
            day: i,
            date: null,
            isCurrentMonth: false,
        });
    }
    
    return days;
});

const updatePosition = () => {
    if (!buttonRef.value) return;
    
    const rect = buttonRef.value.getBoundingClientRect();
    const panelWidth = 288; // w-72 = 18rem = 288px
    const panelHeight = 380;
    const padding = 8;
    
    let left = rect.left;
    let top = rect.bottom + padding;
    
    // Check right overflow
    if (left + panelWidth > window.innerWidth - padding) {
        left = rect.right - panelWidth;
    }
    
    // Check left overflow
    if (left < padding) {
        left = padding;
    }
    
    // Check bottom overflow - show above if needed
    if (top + panelHeight > window.innerHeight - padding) {
        top = rect.top - panelHeight - padding;
    }
    
    panelStyle.value = {
        position: 'fixed',
        left: `${left}px`,
        top: `${top}px`,
        zIndex: 9999,
    };
};

// Close other datepickers when this one opens
const handleGlobalClose = (e) => {
    if (e.detail !== instanceId.value && isOpen.value) {
        isOpen.value = false;
    }
};

const toggleOpen = () => {
    if (isOpen.value) {
        isOpen.value = false;
    } else {
        // Emit event to close other datepickers
        window.dispatchEvent(new CustomEvent('datepicker:open', { detail: instanceId.value }));
        isOpen.value = true;
        nextTick(updatePosition);
    }
};

const closePanel = () => {
    isOpen.value = false;
};

const prevMonthFn = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const nextMonthFn = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const selectDate = (day) => {
    if (day.date) {
        emit('update:modelValue', day.date);
        closePanel();
    }
};

const selectToday = () => {
    emit('update:modelValue', new Date().toISOString().split('T')[0]);
    closePanel();
};

const clearDate = () => {
    emit('update:modelValue', '');
    closePanel();
};

// Close on outside click
const handleClickOutside = (e) => {
    if (!isOpen.value) return;
    
    // Check if click is on the button
    if (buttonRef.value && buttonRef.value.contains(e.target)) {
        return; // Let toggleOpen handle it
    }
    
    // Check if click is on the panel
    if (panelRef.value && panelRef.value.contains(e.target)) {
        return; // Click inside panel, don't close
    }
    
    // Click outside both button and panel - close
    closePanel();
};

// Close on escape
const handleKeydown = (e) => {
    if (e.key === 'Escape' && isOpen.value) {
        closePanel();
    }
};

// Update position on scroll/resize
const handleScroll = () => {
    if (isOpen.value) {
        updatePosition();
    }
};

onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
    window.addEventListener('scroll', handleScroll, true);
    window.addEventListener('resize', handleScroll);
    window.addEventListener('datepicker:open', handleGlobalClose);
});

onUnmounted(() => {
    document.removeEventListener('mousedown', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
    window.removeEventListener('scroll', handleScroll, true);
    window.removeEventListener('resize', handleScroll);
    window.removeEventListener('datepicker:open', handleGlobalClose);
});
</script>

<template>
    <div class="relative">
        <button 
            ref="buttonRef"
            type="button"
            @click="toggleOpen"
            :class="[
                'relative w-full rounded-lg py-2 pl-3 pr-10 text-left text-sm shadow-sm border focus:outline-none focus:ring-2 focus:ring-brand cursor-pointer transition-colors',
                displayValue 
                    ? 'border-gray-300 text-gray-900' 
                    : 'border-gray-300 text-gray-400'
            ]"
        >
            <span class="block truncate">
                {{ displayValue || placeholder }}
            </span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <CalendarDaysIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </span>
        </button>

        <Teleport to="body">
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div 
                    v-if="isOpen"
                    ref="panelRef"
                    :style="panelStyle"
                    class="w-72 rounded-xl bg-white shadow-xl ring-1 ring-black/10 p-4"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <button 
                            type="button" 
                            @click="prevMonthFn" 
                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"
                        >
                            <ChevronLeftIcon class="h-5 w-5" />
                        </button>
                        <span class="text-sm font-semibold text-gray-900">{{ currentMonthYear }}</span>
                        <button 
                            type="button" 
                            @click="nextMonthFn" 
                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-600"
                        >
                            <ChevronRightIcon class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Day headers -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div 
                            v-for="day in dayNames" 
                            :key="day" 
                            class="text-center text-xs font-medium text-gray-500 py-1"
                        >
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar grid -->
                    <div class="grid grid-cols-7 gap-1">
                        <button
                            v-for="(day, idx) in calendarDays"
                            :key="idx"
                            type="button"
                            :disabled="!day.isCurrentMonth"
                            @click="selectDate(day)"
                            :class="[
                                'h-8 w-8 rounded-lg text-sm flex items-center justify-center transition-colors',
                                day.isCurrentMonth ? 'hover:bg-brand/10' : 'text-gray-300 cursor-default',
                                day.isSelected ? 'bg-brand text-white hover:bg-brand' : '',
                                day.isToday && !day.isSelected ? 'ring-2 ring-brand ring-inset text-brand font-semibold' : '',
                                day.isCurrentMonth && !day.isSelected && !day.isToday ? 'text-gray-700' : '',
                            ]"
                        >
                            {{ day.day }}
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                        <button 
                            type="button"
                            @click="clearDate"
                            class="text-xs text-gray-500 hover:text-red-500"
                        >
                            Limpiar
                        </button>
                        <button 
                            type="button"
                            @click="selectToday"
                            class="text-xs text-brand hover:underline font-medium"
                        >
                            Hoy
                        </button>
                    </div>
                </div>
            </transition>
        </Teleport>
    </div>
</template>

