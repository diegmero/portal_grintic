<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

// import ActionSection from '@/Components/ActionSection.vue'; // Removed as it does not exist
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import axios from 'axios';

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const page = usePage();
const form = useForm({
    code: '',
});

const twoFactorEnabled = computed(() => !enabling.value && page.props.auth.user.two_factor_confirmed_at !== null); 
// Note: We need to pass 'two_factor_confirmed_at' in Inertia Shared Props or check existence.
// For now assuming we will add it to HandleInertiaRequests or UserResource.

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    axios.post(route('two-factor.enable')).then(() => {
        Promise.all([
            showQrCode(),
            showRecoveryCodes(),
        ]).then(() => {
            confirming.value = true;
        });
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
        setupKey.value = response.data.secret;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    form.post(route('two-factor.confirm'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;
    axios.delete(route('two-factor.disable')).then(() => {
        disabling.value = false;
        confirming.value = false;
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Two Factor Authentication</h2>
            <p class="mt-1 text-sm text-gray-600">
                Añade seguridad extra a tu cuenta usando autenticación de dos factores.
            </p>
        </header>

        <div class="mt-6">
            <div v-if="twoFactorEnabled">
                <h3 class="text-lg font-medium text-gray-900">
                    Has habilitado la autenticación de dos factores.
                </h3>
                <p class="mt-3 max-w-xl text-sm text-gray-600">
                    Cuando la autenticación de dos factores esté habilitada, se te pedirá un token seguro y aleatorio durante la autenticación. Puedes obtener este token desde la aplicación Google Authenticator de tu teléfono.
                </p>
            </div>

            <div v-else>
                 <h3 class="text-lg font-medium text-gray-900">
                    No tienes habilitada la autenticación de dos factores.
                </h3>
            </div>
            
            <div v-if="enabling">
                 <div class="mt-4">
                    <p class="font-semibold text-gray-900">
                        Para terminar de habilitar la autenticación de dos factores, escanea el siguiente código QR usando la aplicación de autenticación de tu teléfono o ingresa la clave de configuración e ingresa el código OTP generado.
                    </p>
                 </div>

                 <div class="mt-4" v-if="qrCode" v-html="qrCode" />

                 <div class="mt-4 max-w-xl text-sm text-gray-600" v-if="setupKey">
                     Clave de configuración: <span class="font-bold">{{ setupKey }}</span>
                 </div>

                 <div class="mt-4" v-if="confirming">
                     <InputLabel for="code" value="Código" />
                     <TextInput
                         id="code"
                         v-model="form.code"
                         type="text"
                         class="mt-1 block w-1/2"
                         inputmode="numeric"
                         autofocus
                         autocomplete="one-time-code"
                         @keyup.enter="confirmTwoFactorAuthentication"
                     />
                     <InputError :message="form.errors.code" class="mt-2" />
                 </div>
            </div>

            <div class="mt-5">
                <div v-if="! twoFactorEnabled">
                    <PrimaryButton v-if="! confirming" @click="enableTwoFactorAuthentication">
                        Habilitar
                    </PrimaryButton>
                    
                    <PrimaryButton v-else @click="confirmTwoFactorAuthentication" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Confirmar
                    </PrimaryButton>
                </div>
                
                <div v-else>
                     <!-- Options for confirmed users like Show Recovery Codes (omitted for brevity unless requested) -->
                     <DangerButton @click="disableTwoFactorAuthentication" :class="{ 'opacity-25': disabling }" :disabled="disabling">
                        Deshabilitar
                    </DangerButton>
                </div>
            </div>
        </div>
    </section>
</template>
