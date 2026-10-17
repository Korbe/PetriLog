<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import InputError from '@/JetstreamComponents/InputError.vue';
import InputLabel from '@/JetstreamComponents/InputLabel.vue';
import TextInput from '@/JetstreamComponents/TextInput.vue';
import VButton from '@/components/VButton.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>

    <Head title="Zwei-Faktor-Anmeldung" />

    <AuthenticationCard eyebrow="Sicherheit" title="Zwei-Faktor-Anmeldung.">
        <div class="mb-8 leading-relaxed text-(--ink)/65">
            <template v-if="!recovery">
                Bitte bestätige den Zugriff auf dein Konto, indem du den Authentifizierungscode aus deiner
                Authenticator-App eingibst.
            </template>

            <template v-else>
                Bitte bestätige den Zugriff auf dein Konto, indem du einen deiner Notfall-Wiederherstellungscodes
                eingibst.
            </template>
        </div>

        <form @submit.prevent="submit">
            <div v-if="!recovery">
                <InputLabel for="code" value="Code" />
                <TextInput id="code" ref="codeInput" v-model="form.code" type="text" inputmode="numeric"
                    class="form-input mt-1 block w-full" autofocus autocomplete="one-time-code" />
                <InputError class="mt-2" :message="form.errors.code" />
            </div>

            <div v-else>
                <InputLabel for="recovery_code" value="Recovery Code" />
                <TextInput id="recovery_code" ref="recoveryCodeInput" v-model="form.recovery_code" type="text"
                    class="form-input mt-1 block w-full" autocomplete="one-time-code" />
                <InputError class="mt-2" :message="form.errors.recovery_code" />
            </div>

            <div class="mt-8 flex flex-col-reverse items-center gap-4 sm:flex-row sm:justify-between">
                <button type="button" class="text-sm text-(--ink)/65 underline underline-offset-4 cursor-pointer hover:text-(--ink)"
                    @click.prevent="toggleRecovery">
                    <template v-if="!recovery">
                        Einen Wiederherstellungscode verwenden
                    </template>

                    <template v-else>
                        Einen Authentifizierungscode verwenden
                    </template>
                </button>

                <VButton type="submit" class="w-full sm:w-auto" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Anmelden
                </VButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
