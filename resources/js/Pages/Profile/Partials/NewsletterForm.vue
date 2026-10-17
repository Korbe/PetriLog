<script setup>
import { useForm } from '@inertiajs/vue3'
import ActionMessage from '@/JetstreamComponents/ActionMessage.vue'
import PrimaryButton from '@/JetstreamComponents/PrimaryButton.vue'
import VCheckbox from '@/components/VCheckbox.vue'

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
})

const form = useForm({
    newsletter_opt_out: props.user.newsletter_opt_out,
})

const submit = () => {
    form.patch(route('app.profile.newsletter-preferences.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="md:grid md:grid-cols-2 md:gap-6">
        <div class="ui-card overflow-hidden md:col-span-2">
            <form @submit.prevent="submit">
                <!-- CONTENT -->
                <div class="p-5 sm:p-7">

                    <!-- HEADER -->
                    <h1 class="font-display text-3xl text-(--app-ink)">
                        Newsletter
                    </h1>
                    <p class="mt-1 mb-6 text-sm text-(--app-muted)">
                        Verwalte deine Newsletter-Einstellung.
                    </p>

                    <!-- FORM -->
                    <div class="mt-4">
                        <div class="col-span-6">
                            <VCheckbox label="Ich möchte keine Newsletter-Mails mehr erhalten"
                                v-model="form.newsletter_opt_out" :error="form.errors.newsletter_opt_out" />
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center justify-end gap-3 border-t border-(--app-border) bg-(--app-surface-2) px-5 py-4 sm:px-7">
                    <ActionMessage :on="form.recentlySuccessful">
                        Gespeichert.
                    </ActionMessage>

                    <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Speichern
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
