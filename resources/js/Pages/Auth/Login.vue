<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import Checkbox from '@/JetstreamComponents/Checkbox.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VPassword from '@/components/VPassword.vue';
import Cookies from 'js-cookie';
import { ref, onMounted } from 'vue';
import { PencilIcon } from '@heroicons/vue/24/solid';
import InputError from '@/JetstreamComponents/InputError.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showEmailInput = ref(true);
const savedEmail = ref('');

onMounted(() => {
    const emailFromCookie = Cookies.get('petriLoginEmail');
    if (emailFromCookie) {
        savedEmail.value = emailFromCookie;
        showEmailInput.value = false;
        form.email = emailFromCookie; // optional, falls du FormValue brauchst
    }
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => {
            form.reset('password');
            Cookies.set('petriLoginEmail', form.email, { expires: 365, path: '/' });
        },
    });
};

const editEmail = () => {
    showEmailInput.value = true;
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
                <template v-if="showEmailInput">
                    <VInput label="Email" id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                        required autofocus autocomplete="username" :error="form.errors.email" />
                </template>

                <template v-else>
                    <div
                        class="flex items-center justify-center mt-1 p-2 dark:bg-gray-800">
                        <span class="text-gray-700 dark:text-gray-200 truncate">{{ savedEmail }}</span>
                        <button type="button" class="ml-2 text-gray-500 hover:text-gray-700" @click="editEmail">
                            <PencilIcon class="w-5 h-5" />
                        </button>
                    </div>
                    <InputError class="mt-2 text-center" :message="form.errors.email" />
                </template>
            </div>

            <div class="mt-4">
                <VPassword label="Passwort" id="password" v-model="form.password" class="mt-1 block w-full" required
                    autocomplete="current-password" :error="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Angemeldet bleiben</span>
                </label>
            </div>

            <div class="flex w-full justify-center mt-4">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Anmelden
                </VButton>
            </div>

            <div class="mx-auto flex justify-center mt-5">
                <Link class="" :href="route('register')">Neues Konto anlegen</Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
