<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
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

    <AuthenticationCard eyebrow="Passwort" title="Passwort vergessen?">
        <template #intro>Keine Sorge, wir senden dir Anweisungen zum Zurücksetzen deines Passworts zu.</template>

        <div v-if="status" class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
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

            <div class="flex items-center justify-end mt-8">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Passwort zurücksetzen
                </VButton>
            </div>
        </form>

        <p class="mt-8 border-t border-(--ink)/10 pt-6 text-center text-(--ink)/65">
            Doch wieder eingefallen?
            <Link href="/login" class="font-semibold text-(--ink) underline decoration-(--float) decoration-2 underline-offset-4">Zur Anmeldung</Link>
        </p>
    </AuthenticationCard>
</template>
