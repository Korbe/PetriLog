<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/JetstreamComponents/ActionSection.vue';
import DangerButton from '@/JetstreamComponents/DangerButton.vue';
import SecondaryButton from '@/JetstreamComponents/SecondaryButton.vue';
import VInput from '@/components/VInput.vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const openModal = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>

        <template #header>
            <h1 class="text-lg font-medium text-gray-900 dark:text-gray-100">Konto löschen</h1>
            <p class="mt-1 mb-5 text-sm text-gray-600 dark:text-gray-300">Löschen Sie Ihr Konto dauerhaft.</p>
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-300">
                Sobald Ihr Konto gelöscht ist, werden alle darin enthaltenen Ressourcen und Daten dauerhaft gelöscht.
                Laden Sie vor dem Löschen Ihres Kontos bitte alle Daten und Informationen herunter, die Sie behalten
                möchten.
            </div>

            <div class="mt-5">
                <DangerButton @click="openModal">
                    Konto löschen
                </DangerButton>
            </div>

            <Dialog :open="confirmingUserDeletion" @close="closeModal" class="relative z-50">
                <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" aria-hidden="true" />

                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <DialogPanel class="w-full max-w-md rounded bg-white p-6 shadow-xl">
                        <DialogTitle class="text-lg font-bold">Account löschen</DialogTitle>
                        <p class="mt-2 text-sm text-gray-600">Möchten Sie Ihr Konto wirklich löschen? Sobald Ihr Konto
                            gelöscht ist, werden alle darin enthaltenen Ressourcen und Daten dauerhaft gelöscht. Bitte
                            geben Sie Ihr Passwort ein, um die endgültige Löschung Ihres Kontos zu bestätigen.</p>
                        <div class="mt-4">
                            <VInput class="mt-1 w-full" ref="passwordInput" v-model="form.password" type="password"
                                :error="form.errors.password" />
                        </div>

                        <div class="mt-4 flex justify-end">
                            <SecondaryButton @click="closeModal">
                                Abbruch
                            </SecondaryButton>

                            <DangerButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing" @click="deleteUser">
                                Account löschen
                            </DangerButton>
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>
        </template>
    </ActionSection>
</template>
