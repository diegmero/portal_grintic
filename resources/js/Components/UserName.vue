<script setup>
/**
 * Displays a user name with a "(eliminado)" indicator if the user was soft-deleted.
 * Usage: <UserName :user="request.user" />
 */
defineProps({
    user: {
        type: Object,
        default: null,
    },
    fallback: {
        type: String,
        default: 'Usuario desconocido',
    },
});
</script>

<template>
    <span v-if="user">
        {{ user.name }}
        <span v-if="user.deleted_at" class="text-gray-400 text-xs ml-1">(eliminado)</span>
    </span>
    <span v-else class="text-gray-400 italic">{{ fallback }}</span>
</template>
