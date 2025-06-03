<script setup>
import { ref, reactive, nextTick } from 'vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import VInput from '@/components/VInput.vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const emit = defineEmits(['confirmed']);

defineProps({
    title: {
        type: String,
        default: 'Passwort bestätigen',
    },
    content: {
        type: String,
        default: 'Bestätigen Sie zu Ihrer Sicherheit bitte Ihr Passwort, um fortzufahren.',
    },
    button: {
        type: String,
        default: 'Bestätigen',
    },
});

const confirmingPassword = ref(false);

const form = reactive({
    password: '',
    error: '',
    processing: false,
});

const passwordInput = ref(null);

const startConfirmingPassword = () => {
    axios.get(route('password.confirmation')).then(response => {
        if (response.data.confirmed) {
            emit('confirmed');
        } else {
            confirmingPassword.value = true;

            setTimeout(() => passwordInput.value.focus(), 250);
        }
    });
};

const confirmPassword = () => {
    form.processing = true;

    axios.post(route('password.confirm'), {
        password: form.password,
    }).then(() => {
        form.processing = false;

        closeModal();
        nextTick().then(() => emit('confirmed'));

    }).catch(error => {
        form.processing = false;
        form.error = error.response.data.errors.password[0];
        passwordInput.value.focus();
    });
};

const closeModal = () => {
    confirmingPassword.value = false;
    form.password = '';
    form.error = '';
};
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>

        <Dialog :open="confirmingPassword" @close="closeModal" class="relative z-50">
            <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" aria-hidden="true" />

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-md rounded bg-white p-6 shadow-xl">
                    <DialogTitle class="text-lg font-bold">{{ title }}</DialogTitle>
                    <p class="mt-2 text-sm text-gray-600">{{ content }}</p>
                    <div class="mt-4">
                        <VInput class="mt-1 w-full" ref="passwordInput" label="Password" v-model="form.password"
                            type="password" :error="form.error" />
                    </div>

                    <div class="mt-4 flex justify-end">
                        <SecondaryButton @click="closeModal">
                            Abbruch
                        </SecondaryButton>

                        <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing" @click="confirmPassword">
                            {{ button }}
                        </PrimaryButton>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    </span>
</template>
