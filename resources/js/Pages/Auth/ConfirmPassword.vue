<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
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

    <Head title="Passwort bestätigen" />

    <AuthenticationCard eyebrow="Geschützter Bereich" title="Passwort bestätigen.">
        <template #intro>Dies ist ein geschützter Bereich der Anwendung. Bitte bestätige dein Passwort, bevor du fortfährst.</template>

        <form @submit.prevent="submit">
            <div>
                <VInput label="Passwort" id="password" ref="passwordInput" v-model="form.password" type="password"
                    class="mt-1 block w-full" required autocomplete="current-password" :error="form.errors.password" />
            </div>

            <div class="flex justify-end mt-8">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Bestätigen
                </VButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
