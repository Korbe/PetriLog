<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>

    <Head title="Email Verification" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
            <h1 class="font-medium text-2xl mt-3">Email bestätigen</h1>
        </template>

        <div class="space-y-5 mb-4 text-sm text-gray-600">
            <p>Bevor du fortfahrst, bestätige bitte deine E-Mail-Adresse, indem du auf den Link klicken, den wir dir
                gerade per
                E-Mail zugeschickt haben.</p>
            <p>Solltest du die E-Mail nicht erhalten haben, senden wir dir gerne eine neue zu.</p>
        </div>


        <div v-if="verificationLinkSent" class="mb-4 font-medium text-sm text-green-600">
            Ein neuer Bestätigungslink wurde an die E-Mail-Adresse gesendet, die du im Registrierungsformular eingeben hast.
            haben.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Bestätigungs-E-Mail erneut senden
                </PrimaryButton>

                <div>
                    <Link :href="route('logout')" method="post" as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ms-2">
                    Abmelden
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
