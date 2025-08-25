<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import VButton from '@/components/VButton.vue';
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

        <div>
            <h1 class="text-center font-medium text-lg mb-2">Passwort vergessen?</h1>
        </div>

        <div class="mb-4 text-sm text-center text-gray-600">
            Keine Sorge, wir senden dir Anweisungen zum Zurücksetzen deines Passworts zu.
        </div>

        <div v-if="status" class="mb-4 text-center font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <VInput
                label="Email"
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="username"
                    :error="form.errors.email"
                />
            </div>

            <div class="flex items-center justify-end mt-4">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Passwort zurücksetzen
                </VButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
