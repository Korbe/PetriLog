<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import VButton from '@/components/VButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>

    <Head title="E-Mail bestätigen" />

    <AuthenticationCard eyebrow="Fast geschafft" title="E-Mail bestätigen.">
        <template #intro>
            <p>Bitte bestätige deine E-Mail über den Link, den wir dir soeben gesendet haben.</p>
            <p class="mt-3">Keine Mail bekommen? Wir schicken dir gerne eine neue.</p>
        </template>


        <div v-if="verificationLinkSent" class="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            Wir haben dir einen neuen Bestätigungslink geschickt.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <VButton type="submit" class="w-full" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    E-Mail erneut senden
                </VButton>
            </div>
        </form>

        <div class="mt-8 flex justify-center border-t border-(--ink)/10 pt-6">
            <Link href="/logout" method="post" as="button"
                class="text-(--ink)/65 underline underline-offset-4 transition hover:text-(--ink)">
            Abmelden
            </Link>
        </div>
    </AuthenticationCard>
</template>
