<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import Checkbox from '@/JetstreamComponents/Checkbox.vue';
import InputError from '@/JetstreamComponents/InputError.vue';
import InputLabel from '@/JetstreamComponents/InputLabel.vue';
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VPassword from '@/components/VPassword.vue';
import VMultiselect from '@/components/VMultiselect.vue';

defineProps({
    states: Array,
})

const form = useForm({
    name: '',
    email: '',
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

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div>
                <VInput label="Name" id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                    autofocus autocomplete="name" :error="form.errors.name" />
            </div>
            <div class="mt-4">
                <VInput label="Email" id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                    autocomplete="username" :error="form.errors.email" />
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium mb-1">
                    Bundesland (optional)
                </label>

                <VMultiselect v-model="form.state" :options="states" label="name" track-by="id" :multiple="false"
                    placeholder="Bundesland auswählen" :close-on-select="true" :clear-on-select="true"
                    :preserve-search="true" />

                <p v-if="form.errors.state_id" class="text-red-500 text-sm mt-1">
                    {{ form.errors.state_id }}
                </p>
            </div>

            <div class="mt-4">
                <VPassword label="Passwort" id="password" v-model="form.password" class="mt-1 block w-full" required
                    autocomplete="new-password" :error="form.errors.password" :show-forgot="false" />
            </div>

            <div class="mt-4">
                <VPassword label="Passwort bestätigen" id="password_confirmation" v-model="form.password_confirmation"
                    class="mt-1 block w-full" required autocomplete="new-password"
                    :error="form.errors.password_confirmation" :show-forgot="false" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2 dark:text-gray-400">
                            Ich stimme den <a target="_blank" href="/terms-of-service"
                                class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Nutzungsbedingungen</a>
                            und <a target="_blank" href="/privacy-policy"
                                class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Datenschutzerklärung</a>
                            zu.
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    kostenlos Registrieren
                </VButton>
            </div>

            <div class="flex justify-center mt-5 ">
                <Link href="/login" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 rounded-md">
                    Du hast schon ein Konto?
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
