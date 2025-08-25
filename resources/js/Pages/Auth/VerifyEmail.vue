<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';
import VButton from '@/components/VButton.vue';

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
        </template>

        <div>
            <h1 class="text-center font-medium text-lg mb-2">Email Adresse bestätigen</h1>
        </div>

        <div class="space-y-5 mb-4 text-center text-gray-600">
            <p>Bitte bestätige deine E-Mail über den Link, den wir dir soeben gesendet haben.</p>
            <p>Keine Mail bekommen?<br> Wir schicken dir gerne eine neue.</p>
        </div>


        <div v-if="verificationLinkSent" class="mb-4 font-medium text-center text-sm text-green-600">
            Wir haben dir einen neuen Bestätigungslink geschickt.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    E-Mail erneut senden
                </VButton>
            </div>
        </form>

        <div class="flex justify-center mt-5">
            <Link :href="route('logout')" method="post" as="button"
                class="text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ms-2">
            Abmelden
            </Link>
        </div>
    </AuthenticationCard>
</template>
