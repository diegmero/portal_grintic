<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    hCaptchaSiteKey: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
    'h-captcha-response': '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
            // Reset captcha if exists
             if (window.hcaptcha) {
                window.hcaptcha.reset();
                form['h-captcha-response'] = '';
            }
        },
    });
};

onMounted(() => {
    const renderCaptcha = () => {
        if (window.hcaptcha) {
            try {
                const container = document.getElementById('hcaptcha-container');
                if (container) {
                     // Empty container to prevent duplicates
                    container.innerHTML = '';
                    window.hcaptcha.render(container, {
                        sitekey: props.hCaptchaSiteKey,
                        callback: (token) => {
                            form['h-captcha-response'] = token;
                        }
                    });
                }
            } catch (e) {
                console.error("hCaptcha render error", e);
            }
        }
    };

    let checkInterval;

    if (typeof window.hcaptcha !== 'undefined') {
        renderCaptcha();
    } else {
        // Fallback if script is slow to load
        checkInterval = setInterval(() => {
             if (typeof window.hcaptcha !== 'undefined') {
                 clearInterval(checkInterval);
                 renderCaptcha();
             }
        }, 100);
    }
});

onUnmounted(() => {
     if (typeof checkInterval !== 'undefined') {
         clearInterval(checkInterval);
     }
});
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <!-- hCaptcha Widget -->
            <div class="mt-4 flex justify-center">
                <div id="hcaptcha-container"></div>
            </div>
             <InputError class="mt-2 text-center" :message="form.errors['h-captcha-response']" />

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
