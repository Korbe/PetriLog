<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import Checkbox from '@/JetstreamComponents/Checkbox.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VPassword from '@/components/VPassword.vue';
import Cookies from 'js-cookie';
import { ref, onMounted } from 'vue';
import { PencilIcon } from '@heroicons/vue/24/solid';
import InputError from '@/JetstreamComponents/InputError.vue';
import { initAnalytics, initGoogleMaps } from '@/google.js';

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

    localStorage.setItem('cookie-consent', 'accepted')

    initAnalytics();
    initGoogleMaps();

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

    <AuthenticationCard eyebrow="Anmelden" title="Willkommen zurück.">
        <template #intro>Petri Heil! Melde dich an und trag deinen nächsten Fang ein.</template>

        <div v-if="status" class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <template v-if="showEmailInput">
                    <VInput label="Email" id="email" v-model="form.email" type="email" class="mt-1 block w-full"
                        required autofocus autocomplete="username" :error="form.errors.email" />
                </template>

                <template v-else>
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-(--ink)/10 bg-white px-4 py-3">
                        <div class="min-w-0">
                            <p class="font-log text-[10px] uppercase tracking-[0.2em] text-(--ink)/50">Angemeldet als</p>
                            <p class="truncate font-medium">{{ savedEmail }}</p>
                        </div>
                        <button type="button" class="shrink-0 rounded-full p-2 text-(--ink)/50 transition hover:bg-(--paper) hover:text-(--ink)" aria-label="E-Mail ändern" @click="editEmail">
                            <PencilIcon class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError class="mt-2 text-center" :message="form.errors.email" />
                </template>
            </div>

            <div class="mt-5">
                <VPassword label="Passwort" id="password" v-model="form.password" class="mt-1 block w-full" required
                    autocomplete="current-password" :error="form.errors.password" />
            </div>

            <div class="block mt-5">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-(--ink)/70">Angemeldet bleiben</span>
                </label>
            </div>

            <div class="flex w-full justify-center mt-8">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Anmelden
                </VButton>
            </div>

            <p class="mt-8 border-t border-(--ink)/10 pt-6 text-center text-(--ink)/65">
                Noch kein Fangbuch?
                <Link class="font-semibold text-(--ink) underline decoration-(--float) decoration-2 underline-offset-4" href="/register">Kostenlos registrieren</Link>
            </p>
        </form>
    </AuthenticationCard>
</template>
