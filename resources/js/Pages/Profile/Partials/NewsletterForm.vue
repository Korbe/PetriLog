<script setup>
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue';
import FormSection from '@/JetstreamComponents/FormSection.vue';
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue';
import VCheckbox from '@/components/VCheckbox.vue';

const props = defineProps({
    user: Object,
});

const form = useForm({
    newsletter_opt_out: props.user.newsletter_opt_out
});

const updateNewsletterPreferences = () => {
    form.patch(route('user-newsletter-preferences.update'));
};;
</script>

<template>
    <FormSection @submitted="updateNewsletterPreferences">
        <template #header>
            <h1 class="text-lg font-medium text-gray-800 dark:text-gray-100">Newsletter</h1>
            <p class="mt-1 mb-5 text-sm text-gray-600 dark:text-gray-300">Verwalten deine Newsletter-Einstellungen.</p>
        </template>

        <template #form>

            <VCheckbox label="Ich möchte keine Newsletter Mails mehr erhalten" v-model="form.newsletter_opt_out"
                :error="form.errors.newsletter_opt_out"></VCheckbox>

        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Gespeichert.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Speichern
            </PrimaryButton>
        </template>
    </FormSection>
</template>
