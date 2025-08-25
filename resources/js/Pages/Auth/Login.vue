<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import Checkbox from '@/JetstreamComponents/Checkbox.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VPassword from '@/components/VPassword.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
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

            <div class="mt-4">
                <VPassword
                    label="Passwort"
                    id="password"
                    v-model="form.password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    :error="form.errors.password"
                />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Angemeldet bleiben</span>
                </label>
            </div>

            <div class="flex w-full justify-center mt-4">
                <!-- <Link v-if="canResetPassword" :href="route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Passwort vergessen?
                </Link> -->

                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Anmelden
                </VButton>
            </div>

            <div class="mx-auto flex justify-center mt-5">
                <Link class="" :href="route('register')">Neues Konto anlegen</Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
