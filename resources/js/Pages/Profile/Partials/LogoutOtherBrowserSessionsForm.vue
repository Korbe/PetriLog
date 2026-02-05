<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue';
import ActionSection from '@/JetstreamComponents/ActionSection.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';
import SecondaryButton from '@/JetstreamComponents/SecondaryButton.vue';
import VInput from '@/components/VInput.vue';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmLogout = () => {
    confirmingLogout.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>

        <template #header>
            <h1 class="text-lg font-medium text-gray-900 dark:text-gray-100">Browsersitzungen</h1>
            <p class="mt-1 mb-5 text-sm text-gray-600 dark:text-gray-300">Verwalte und melde dich von deinen aktiven Sitzungen auf anderen Browsern und Geräten ab.</p>
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-300">
                Bei Bedarf kannst du dich von allen anderen Browsersitzungen auf allen deinen Geräten abmelden. Einige
                deiner letzten Sitzungen sind unten aufgeführt; diese Liste ist jedoch möglicherweise nicht vollständig.
                Wenn du glaubst, dass dein Konto kompromittiert wurde, solltest du auch dein Passwort aktualisieren.
            </div>

            <!-- Other Browser Sessions -->
            <div v-if="sessions.length > 0" class="mt-5 space-y-6">
                <div v-for="(session, i) in sessions" :key="i" class="flex items-center">
                    <div>
                        <svg v-if="session.agent.is_desktop" class="size-8 text-gray-500 dark:text-gray-300"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                        </svg>

                        <svg v-else class="size-8 text-gray-500 dark:text-gray-300" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>

                    <div class="ms-3">
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            {{ session.agent.platform ? session.agent.platform : 'Unknown' }} - {{ session.agent.browser
                                ? session.agent.browser : 'Unknown' }}
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-300">
                                {{ session.ip_address }},

                                <span v-if="session.is_current_device" class="text-green-500 font-semibold">Dieses Gerät</span>
                                <span v-else>Zuletzt aktiv {{ session.last_active }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center mt-5">
                <PrimaryButton @click="confirmLogout">
                    Andere Browsersitzungen abmelden
                </PrimaryButton>

                <ActionMessage :on="form.recentlySuccessful" class="ms-3">
                    Fertig.
                </ActionMessage>
            </div>


            <Dialog :open="confirmingLogout" @close="closeModal" class="relative z-50">
                <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" aria-hidden="true" />

                <div class="fixed inset-0 flex items-center justify-center p-4">
                    <DialogPanel class="w-full max-w-md rounded bg-white p-6 shadow-xl">
                        <DialogTitle class="text-lg font-bold">Andere Browsersitzungen abmelden</DialogTitle>
                        <p class="mt-2 text-sm text-gray-600">Bitte geben Sie Ihr Passwort ein, um zu bestätigen, dass
                            Sie sich von Ihren anderen Browsersitzungen auf allen Ihren Geräten abmelden möchten.</p>
                        <div class="mt-4">
                            <VInput class="mt-1 w-full" ref="passwordInput" v-model="form.password" type="password"
                                :error="form.errors.password" />
                        </div>

                        <div class="mt-4 flex justify-end">
                            <SecondaryButton @click="closeModal">
                                Abbruch
                            </SecondaryButton>

                            <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing" @click="logoutOtherBrowserSessions">
                                abmelden
                            </PrimaryButton>
                        </div>
                    </DialogPanel>
                </div>
            </Dialog>
        </template>
    </ActionSection>
</template>
