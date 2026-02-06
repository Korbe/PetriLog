<template>

    <Head title="Willkommen" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div>
            <h1 class="text-center font-medium text-lg mb-2 dark:text-gray-400">Herzlich Willkommen {{ $page.props.auth.user.name }}</h1>
        </div>

        <div class="space-y-5 mb-4 text-center text-gray-600 dark:text-gray-400">
            <p>Danke, dass du dich registriert hast!</p>
            <p>Du nutzt gerade den Gratis-Plan und kannst bis zu 5 Fänge eintragen. Danach freuen wir uns, wenn du auf
                ein Abo umsteigst.</p>
        </div>

        <div class="mb-4 font-medium text-center text-sm text-green-600">
            Du kannst jetzt loslegen und deinen ersten Fang eintragen.
        </div>

        <div class="flex justify-center mt-5">
            <VButton href="/dashboard">
                Weiter zum Dashboard
            </VButton>
        </div>
    </AuthenticationCard>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/JetstreamComponents/AuthenticationCardLogo.vue';
import VButton from '@/components/VButton.vue';
</script>