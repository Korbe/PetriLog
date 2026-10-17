<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import VInput from '@/components/VInput.vue';
import VPassword from '@/components/VPassword.vue';
import VButton from '@/components/VButton.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Passwort zurücksetzen" />

    <AuthenticationCard eyebrow="Passwort" title="Neues Passwort festlegen.">

        <form @submit.prevent="submit">
            <div>
                <VInput label="Email" id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                    autofocus autocomplete="username" :error="form.errors.email" />
            </div>

            <div class="mt-4">
                <VPassword label="Passwort" id="password" v-model="form.password" class="mt-1 block w-full" required
                    :show-forgot="false" autocomplete="new-password" :error="form.errors.password" />
            </div>

            <div class="mt-4">
                <VPassword label="Passwort bestätigen" id="password_confirmation" v-model="form.password_confirmation"
                    :show-forgot="false" class="mt-1 block w-full" required autocomplete="new-password"
                    :error="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-8">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Passwort zurücksetzen
                </VButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
