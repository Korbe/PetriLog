<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue';
import FormSection from '@/JetstreamComponents/FormSection.vue';
import InputError from '@/JetstreamComponents/InputError.vue';
import InputLabel from '@/JetstreamComponents/InputLabel.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';
import SecondaryButton from '@/JetstreamComponents/SecondaryButton.vue';
import VInput from '@/components/VInput.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => clearPhotoFileInput(),
    });
};

const sendEmailVerification = () => {
    verificationLinkSent.value = true;
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <FormSection @submitted="updateProfileInformation">
        <template #header>
            <h1 class="text-lg font-medium text-gray-800 dark:text-gray-100">Profil Information</h1>
            <p class="mt-1 mb-5 text-sm text-gray-600 dark:text-gray-300">Aktualisieren Sie die Profilinformationen und
                E-Mail-Adresse Ihres Kontos.</p>
        </template>

        <template #form>


            <!-- Profile Photo -->
            <div v-if="$page.props.jetstream.managesProfilePhotos" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input id="photo" ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview">

                <InputLabel for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div v-show="!photoPreview" class="mt-2">
                    <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div v-show="photoPreview" class="mt-2">
                    <span class="block rounded-full size-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'" />
                </div>

                <SecondaryButton class="mt-2 me-2" type="button" @click.prevent="selectNewPhoto">
                    Select A New Photo
                </SecondaryButton>

                <SecondaryButton v-if="user.profile_photo_path" type="button" class="mt-2" @click.prevent="deletePhoto">
                    Remove Photo
                </SecondaryButton>

                <InputError :message="form.errors.photo" class="mt-2" />
            </div>

            <!-- Name -->
            <div class="col-span-6 sm:col-span-4">
                <VInput id="name" label="Name" v-model="form.name" mandatory :error="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="col-span-6 sm:col-span-4">

                <VInput id="email" label="Email" v-model="form.email" mandatory :error="form.errors.email" />
                <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at == null">
                    <p class="text-sm mt-2">
                        Ihre E-Mail-Adresse ist nicht bestätigt.

                        <Link href="/email/verification-notification" method="post" as="button"
                            class="cursor-pointer underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                            @click.prevent="sendEmailVerification">
                        Klicken Sie hier, um die Bestätigungs-E-Mail erneut zu senden.
                        </Link>
                    </p>

                    <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-green-600">
                        Ein neuer Bestätigungslink wurde an Ihre E-Mail-Adresse gesendet.
                    </div>
                </div>
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Gespeichert.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Speichern
            </PrimaryButton>
        </template>
    </FormSection>
</template>
