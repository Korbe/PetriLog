<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import Checkbox from '@/JetstreamComponents/Checkbox.vue';
import InputError from '@/JetstreamComponents/InputError.vue';
import InputLabel from '@/JetstreamComponents/InputLabel.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VPassword from '@/components/VPassword.vue';
import VCombobox from '@/components/VCombobox.vue';

defineProps({
    states: Array,
})

const form = useForm({
    name: '',
    email: '',
    tel: '',
    password: '',
    password_confirmation: '',
    terms: false,
    state_id: null,
    state: null,
});

const submit = () => {

    form.state_id = form.state ? form.state.id : null;
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Registrieren" />

    <AuthenticationCard eyebrow="Kostenlos registrieren" title="Leg dein Fangbuch an.">
        <template #intro>Bis zu 5 Fänge kostenlos – mit allen Funktionen. Upgraden kannst du jederzeit.</template>

        <form @submit.prevent="submit">
            <div>
                <VInput label="Name" id="name" v-model="form.name" type="text" class="mt-1 block w-full" mandatory
                    autofocus autocomplete="name" :error="form.errors.name" />
            </div>
            <div class="mt-4">
                <VInput label="Email" id="email" v-model="form.email" type="email" class="mt-1 block w-full" mandatory
                    autocomplete="email" :error="form.errors.email" />
            </div>
            <div class="mt-4">
                <VInput label="Telefonnummer" id="tel" v-model="form.tel" type="tel" class="mt-1 block w-full"
                    autocomplete="tel" :error="form.errors.tel" />
            </div>

            <div class="mt-4">
                <label class="block mb-1">
                    Bundesland
                </label>

                <VCombobox v-model="form.state" :options="states" label="name" track-by="id"
                    placeholder="Bundesland auswählen" search-placeholder="Bundesland suchen …" aria-label="Bundesland" />

                <p v-if="form.errors.state_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.state_id }}
                </p>
            </div>

            <div class="mt-4">
                <VPassword label="Passwort" id="password" v-model="form.password" class="mt-1 block w-full" mandatory
                    autocomplete="new-password" :error="form.errors.password" :show-forgot="false" />
            </div>

            <div class="mt-4">
                <VPassword label="Passwort bestätigen" id="password_confirmation" mandatory
                    v-model="form.password_confirmation" class="mt-1 block w-full" required autocomplete="new-password"
                    :error="form.errors.password_confirmation" :show-forgot="false" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2 text-sm font-normal text-(--ink)/70">
                            Ich stimme den <a target="_blank" href="/terms-of-service"
                                class="font-medium text-(--ink) underline underline-offset-4">Nutzungsbedingungen</a>
                            und <a target="_blank" href="/privacy-policy"
                                class="font-medium text-(--ink) underline underline-offset-4">Datenschutzerklärung</a>
                            zu.
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-8">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Kostenlos registrieren
                </VButton>
            </div>

            <p class="mt-8 border-t border-(--ink)/10 pt-6 text-center text-(--ink)/65">
                Du hast schon ein Konto?
                <Link href="/login" class="font-semibold text-(--ink) underline decoration-(--float) decoration-2 underline-offset-4">Anmelden</Link>
            </p>
        </form>
    </AuthenticationCard>
</template>
