<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue';
import ActionSection from '@/JetstreamComponents/ActionSection.vue';
import Checkbox from '@/JetstreamComponents/Checkbox.vue';
import ConfirmationModal from '@/JetstreamComponents/ConfirmationModal.vue';
import DangerButton from '@/JetstreamComponents/DangerButton.vue';
import FormSection from '@/JetstreamComponents/FormSection.vue';
import InputError from '@/JetstreamComponents/InputError.vue';
import InputLabel from '@/JetstreamComponents/InputLabel.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';
import SecondaryButton from '@/JetstreamComponents/SecondaryButton.vue';
import SectionBorder from '@/JetstreamComponents/SectionBorder.vue';
import TextInput from '@/JetstreamComponents/TextInput.vue';

const props = defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});

const createApiTokenForm = useForm({
    name: '',
    permissions: props.defaultPermissions,
});

const updateApiTokenForm = useForm({
    permissions: [],
});

const deleteApiTokenForm = useForm({});

const displayingToken = ref(false);
const managingPermissionsFor = ref(null);
const apiTokenBeingDeleted = ref(null);

const createApiToken = () => {
    createApiTokenForm.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            displayingToken.value = true;
            createApiTokenForm.reset();
        },
    });
};

const manageApiTokenPermissions = (token) => {
    updateApiTokenForm.permissions = token.abilities;
    managingPermissionsFor.value = token;
};

const updateApiToken = () => {
    updateApiTokenForm.put(route('api-tokens.update', managingPermissionsFor.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => (managingPermissionsFor.value = null),
    });
};

const confirmApiTokenDeletion = (token) => {
    apiTokenBeingDeleted.value = token;
};

const deleteApiToken = () => {
    deleteApiTokenForm.delete(route('api-tokens.destroy', apiTokenBeingDeleted.value), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => (apiTokenBeingDeleted.value = null),
    });
};
</script>

<template>
    <div>
        <!-- Generate API Token -->
        <FormSection @submitted="createApiToken">
            <template #title>
                Create API Token
            </template>

            <template #description>
                API tokens allow third-party services to authenticate with our application on your behalf.
            </template>

            <template #form>
                <!-- Token Name -->
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="name" value="Name" />
                    <TextInput id="name" v-model="createApiTokenForm.name" type="text" class="mt-1 block w-full"
                        autofocus />
                    <InputError :message="createApiTokenForm.errors.name" class="mt-2" />
                </div>

                <!-- Token Permissions -->
                <div v-if="availablePermissions.length > 0" class="col-span-6">
                    <InputLabel for="permissions" value="Permissions" />

                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="permission in availablePermissions" :key="permission">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="createApiTokenForm.permissions" :value="permission" />
                                <span class="ms-2 text-sm text-gray-600">{{ permission }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </template>

            <template #actions>
                <ActionMessage :on="createApiTokenForm.recentlySuccessful" class="me-3">
                    Created.
                </ActionMessage>

                <PrimaryButton :class="{ 'opacity-25': createApiTokenForm.processing }"
                    :disabled="createApiTokenForm.processing">
                    Create
                </PrimaryButton>
            </template>
        </FormSection>

        <div v-if="tokens.length > 0">
            <SectionBorder />

            <!-- Manage API Tokens -->
            <div class="mt-10 sm:mt-0">
                <ActionSection>
                    <template #title>
                        Manage API Tokens
                    </template>

                    <template #description>
                        You may delete any of your existing tokens if they are no longer needed.
                    </template>

                    <!-- API Token List -->
                    <template #content>
                        <div class="space-y-6">
                            <div v-for="token in tokens" :key="token.id" class="flex items-center justify-between">
                                <div class="break-all">
                                    {{ token.name }}
                                </div>

                                <div class="flex items-center ms-2">
                                    <div v-if="token.last_used_ago" class="text-sm text-gray-400">
                                        Last used {{ token.last_used_ago }}
                                    </div>

                                    <button v-if="availablePermissions.length > 0"
                                        class="cursor-pointer ms-6 text-sm text-gray-400 underline"
                                        @click="manageApiTokenPermissions(token)">
                                        Permissions
                                    </button>

                                    <button class="cursor-pointer ms-6 text-sm text-red-500"
                                        @click="confirmApiTokenDeletion(token)">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </ActionSection>
            </div>
        </div>

        <!-- Token Value Modal -->
        <Dialog :open="displayingToken" @close="displayingToken = false" class="relative z-50">
            <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" aria-hidden="true" />

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-md rounded bg-white p-6 shadow-xl">
                    <DialogTitle class="text-lg font-bold">API Token</DialogTitle>
                    <p class="mt-2 text-sm text-gray-600">Bitte kopieren Sie Ihren neuen API-Token. Aus
                        Sicherheitsgründen wird
                        er nicht erneut angezeigt.</p>
                    <div v-if="$page.props.jetstream.flash.token"
                        class="mt-4 bg-gray-100 px-4 py-2 rounded font-mono text-sm text-gray-500 break-all">
                        {{ $page.props.jetstream.flash.token }}
                    </div>

                    <div class="mt-4 flex justify-end">
                        <SecondaryButton @click="displayingToken = false">
                            Schließen
                        </SecondaryButton>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>

        <!-- API Token Permissions Modal -->
        <Dialog :open="managingPermissionsFor" @close="managingPermissionsFor = null" class="relative z-50">
            <div class="fixed inset-0 bg-black/30 backdrop-blur-sm" aria-hidden="true" />

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel class="w-full max-w-md rounded bg-white p-6 shadow-xl">
                    <DialogTitle class="text-lg font-bold">API Token Berechtigungen</DialogTitle>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="permission in availablePermissions" :key="permission">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="updateApiTokenForm.permissions" :value="permission" />
                                <span class="ms-2 text-sm text-gray-600">{{ permission }}</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <SecondaryButton @click="managingPermissionsFor = null">
                            Abbruch
                        </SecondaryButton>

                        <PrimaryButton class="ms-3" :class="{ 'opacity-25': updateApiTokenForm.processing }"
                            :disabled="updateApiTokenForm.processing" @click="updateApiToken">
                            Speichern
                        </PrimaryButton>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>

        <!-- Delete Token Confirmation Modal -->
        <ConfirmationModal :show="apiTokenBeingDeleted != null" @close="apiTokenBeingDeleted = null">
            <template #title>
                API-Token löschen
            </template>

            <template #content>
                Möchten Sie dieses API-Token wirklich löschen?
            </template>

            <template #footer>
                <SecondaryButton @click="apiTokenBeingDeleted = null">
                    Cancel
                </SecondaryButton>

                <DangerButton class="ms-3" :class="{ 'opacity-25': deleteApiTokenForm.processing }"
                    :disabled="deleteApiTokenForm.processing" @click="deleteApiToken">
                    Löschen
                </DangerButton>
            </template>
        </ConfirmationModal>
    </div>
</template>
