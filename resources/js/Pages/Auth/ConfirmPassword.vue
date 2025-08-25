<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>

    <Head title="Secure Area" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600">
            Dies ist ein geschützter Bereich der Anwendung. Bitte bestätige dein Passwort, bevor du fortfährst.
        </div>

        <form @submit.prevent="submit">
            <div>
                <VInput label="Passwort" id="password" ref="passwordInput" v-model="form.password" type="password"
                    class="mt-1 block w-full" required autocomplete="current-password" :error="form.errors.password" />
            </div>

            <div class="flex justify-end mt-4">
                <VButton type="submit" class="ms-4" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Bestätige
                </VButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
