<script setup>
defineProps({
    variant: {
        type: String,
        default: 'dark', // 'dark' = dark logo for light bg, 'light' = light logo for dark bg
    },
});
</script>

<template>
    <img 
        v-if="variant === 'light'" 
        src="/images/logo-light.png" 
        alt="GrinTic" 
        class="w-auto"
    />
    <img 
        v-else 
        src="/images/logo-dark.png" 
        alt="GrinTic" 
        class="w-auto"
    />
</template>
