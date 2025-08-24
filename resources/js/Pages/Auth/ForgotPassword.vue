<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import InputError from '@/JetstreamComponents/InputError.vue';
import InputLabel from '@/JetstreamComponents/InputLabel.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';
import TextInput from '@/JetstreamComponents/TextInput.vue';
import VInput from '@/components/VInput.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Passwort vergessen?" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600">
            Passwort vergessen? Kein Problem. Teile uns einfach deine E-Mail-Adresse mit und wir senden dir per E-Mail einen Link zum Zurücksetzen deines Passworts, mit dem du ein neues Passwort festlegen kannst.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Link zum Zurücksetzen senden
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
