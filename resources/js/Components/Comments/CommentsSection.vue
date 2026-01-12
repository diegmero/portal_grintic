<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { PaperAirplaneIcon, PencilIcon, TrashIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    commentableId: String,
    commentableType: String,
    initialComments: Array,
});

const comments = ref([...props.initialComments]);
const currentUser = usePage().props.auth.user;
const isAdmin = !currentUser?.company_id;
const canCreateComments = computed(() => {
    return isAdmin || currentUser?.permissions?.some(p => p.name === 'create_comments') || false;
});

// Edit state
const editingCommentId = ref(null);
const editingBody = ref('');

// Watch for prop changes (when Inertia reloads the page)
watch(() => props.initialComments, (newComments) => {
    comments.value = [...newComments];
}, { deep: true });

const form = useForm({
    body: '',
    commentable_id: props.commentableId,
    commentable_type: props.commentableType,
});

const submit = () => {
    // Optimistic Update
    const tempId = Date.now();
    const newComment = {
        id: tempId,
        body: form.body,
        user: currentUser,
        created_at: new Date().toISOString(),
        isOptimistic: true,
    };
    
    comments.value.push(newComment);
    
    form.post(route('comments.store'), {
        preserveScroll: true,
        onSuccess: () => {
             form.reset();
        },
        onError: () => {
            // Remove optimistic comment
            comments.value = comments.value.filter(c => c.id !== tempId);
        }
    });
};

// Edit functionality
const startEdit = (comment) => {
    editingCommentId.value = comment.id;
    editingBody.value = comment.body;
};

const cancelEdit = () => {
    editingCommentId.value = null;
    editingBody.value = '';
};

const saveEdit = (comment) => {
    router.patch(route('comments.update', comment.id), {
        body: editingBody.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local state
            const idx = comments.value.findIndex(c => c.id === comment.id);
            if (idx !== -1) {
                comments.value[idx].body = editingBody.value;
            }
            cancelEdit();
        }
    });
};

const deleteComment = (commentId) => {
    if (confirm('¿Eliminar este comentario?')) {
        router.delete(route('comments.destroy', commentId), {
            preserveScroll: true,
            onSuccess: () => {
                comments.value = comments.value.filter(c => c.id !== commentId);
            }
        });
    }
};

const canEditComment = (comment) => {
    return isAdmin || comment.user_id === currentUser?.id;
};

let echoChannel = null;

onMounted(() => {
    // Echo Listener - sanitize channel name (Pusher doesn't allow backslashes)
    // Wrapped in try-catch to prevent errors when websocket server is unavailable
    try {
        if (window.Echo) {
            const sanitizedType = props.commentableType.replace(/\\/g, '.');
            const channelName = `comments.${sanitizedType}.${props.commentableId}`;
            
            echoChannel = window.Echo.private(channelName);
            
            // Listen for the CommentCreated event
            echoChannel.listen('.CommentCreated', (e) => {
                // Check if we already have this comment (prevent duplicates from optimistic UI)
                const exists = comments.value.some(c => c.id === e.id);
                if (!exists) {
                    comments.value.push(e);
                }
            });
        }
    } catch (error) {
        console.warn('Echo connection failed:', error);
    }
});

onUnmounted(() => {
    // Clean up the Echo subscription
    if (echoChannel) {
        try {
            echoChannel.stopListening('.CommentCreated');
            window.Echo.leave(`comments.${props.commentableType.replace(/\\/g, '.')}.${props.commentableId}`);
        } catch (error) {
            console.warn('Echo cleanup failed:', error);
        }
    }
});
</script>

<template>
    <div>
        
        <div class="space-y-4 max-h-60 overflow-y-auto mb-4 bg-gray-50 p-3 rounded-lg">
            <div v-for="comment in comments" :key="comment.id" class="flex gap-3 group">
                <div class="flex-shrink-0">
                     <div class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center text-xs font-bold text-gray-600">
                        {{ comment.user.name.charAt(0) }}
                    </div>
                </div>
                <div class="flex-1 space-y-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-medium text-gray-900">{{ comment.user.name }}</h3>
                        <div class="flex items-center gap-2">
                            <p class="text-[10px] text-gray-500">{{ new Date(comment.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</p>
                            
                            <!-- Action buttons (visible on hover for admin or owner) -->
                            <div v-if="canEditComment(comment) && editingCommentId !== comment.id" class="hidden group-hover:flex items-center gap-1">
                                <button @click="startEdit(comment)" class="p-1 text-gray-400 hover:text-brand rounded" title="Editar">
                                    <PencilIcon class="h-3 w-3" />
                                </button>
                                <button v-if="isAdmin" @click="deleteComment(comment.id)" class="p-1 text-gray-400 hover:text-red-500 rounded" title="Eliminar">
                                    <TrashIcon class="h-3 w-3" />
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Display or Edit mode -->
                    <template v-if="editingCommentId === comment.id">
                        <div class="flex items-center gap-2">
                            <input 
                                v-model="editingBody" 
                                type="text" 
                                class="flex-1 text-xs rounded border-gray-300 py-1 px-2 focus:ring-brand focus:border-brand"
                                @keyup.enter="saveEdit(comment)"
                                @keyup.escape="cancelEdit"
                            />
                            <button @click="saveEdit(comment)" class="p-1 text-green-600 hover:text-green-700">
                                <CheckIcon class="h-4 w-4" />
                            </button>
                            <button @click="cancelEdit" class="p-1 text-gray-400 hover:text-gray-600">
                                <XMarkIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </template>
                    <template v-else>
                        <p class="text-xs text-gray-700">{{ comment.body }}</p>
                    </template>
                </div>
            </div>
             <p v-if="comments.length === 0" class="text-xs text-center text-gray-400 py-2">Sin comentarios.</p>
        </div>

        <form v-if="canCreateComments" @submit.prevent="submit" class="relative">
            <input 
                v-model="form.body"
                type="text" 
                class="block w-full rounded-md border-0 py-2 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand sm:text-sm sm:leading-6" 
                placeholder="Escribe un comentario..."
            />
            <button type="submit" :disabled="form.processing" class="absolute inset-y-0 right-0 flex items-center pr-3 text-brand hover:text-blue-700">
                <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
            </button>
        </form>
        <p v-else class="text-xs text-center text-gray-400 py-2 border-t border-gray-100 italic">
            No tienes permisos para comentar.
        </p>
    </div>
</template>

