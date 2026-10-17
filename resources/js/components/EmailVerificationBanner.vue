<script setup>
import { ref, onMounted } from 'vue'
import Cookies from 'js-cookie'
import { useForm, usePage } from '@inertiajs/vue3'
import VButton from './VButton.vue'

const page = usePage()
const user = page.props.auth?.user

const showBanner = ref(false)
const showSuccessMessage = ref(false)
const cookieName = 'email_verification_banner_dismissed'
const cookieDurationHours = 24

function dismissBanner() {
    Cookies.set(cookieName, '1', { expires: cookieDurationHours / 24 })
    showBanner.value = false
}

onMounted(() => {
    if (user?.verified) return
    if (Cookies.get(cookieName)) return // Schon dismissed → nicht zeigen
    showBanner.value = true
})

const form = useForm({})

const sendAgain = () => {
    showSuccessMessage.value = false // Reset bei neuem Klick
    form.post(route('verification.send'), {
        onSuccess: () => {
            showSuccessMessage.value = true
        },
    })
}
</script>

<template>
    <div v-if="showBanner"
        class="ui-card relative flex flex-col gap-4 p-5 pr-12 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                    stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </span>
            <div class="text-sm">
                <p class="font-semibold">Bitte bestätige deine E-Mail-Adresse.</p>
                <p class="ui-muted mt-0.5">Überprüfe dein Postfach – wir haben dir einen Link geschickt.</p>
                <p v-if="showSuccessMessage" class="mt-1 font-medium text-(--app-success)">
                    Ein neuer Bestätigungslink ist auf dem Weg zu dir.
                </p>
            </div>
        </div>

        <VButton variant="secondary" size="sm" @click="sendAgain" :disabled="form.processing" :loading="form.processing"
            class="self-start sm:self-center">
            Erneut senden
        </VButton>

        <button @click="dismissBanner" class="ui-icon-btn absolute right-2 top-2 size-8!" aria-label="Hinweis ausblenden">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 9.293l3.646-3.647a.5.5 0 11.708.708L10.707 10l3.647 3.646a.5.5 0 11-.708.708L10 10.707l-3.646 3.647a.5.5 0 11-.708-.708L9.293 10 5.646 6.354a.5.5 0 11.708-.708L10 9.293z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>
