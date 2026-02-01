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
    form.patch(route('user-newsletter-preferences.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="md:grid md:grid-cols-2 md:gap-6">
        <div class="mt-5 md:mt-0 md:col-span-2 rounded-lg bg-white dark:bg-gray-800">
            <form @submit.prevent="submit">
                <!-- CONTENT -->
                <div class="px-4 py-5 sm:p-6">

                    <!-- HEADER -->
                    <h1 class="text-lg font-medium text-gray-800 dark:text-gray-100">
                        Newsletter
                    </h1>
                    <p class="mt-1 mb-5 text-sm text-gray-600 dark:text-gray-300">
                        Verwalte deine Newsletter-Einstellungen.
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
                <div class="rounded-lg flex items-center px-4 py-3 shadow">
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
