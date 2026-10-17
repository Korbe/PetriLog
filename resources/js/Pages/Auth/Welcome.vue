<template>

    <Head title="Willkommen" />

    <AuthenticationCard eyebrow="Petri Heil!" :title="`Willkommen, ${$page.props.auth.user.name}.`">
        <template #intro>
            <p>Danke, dass du dich registriert hast!</p>
            <p class="mt-3">Du nutzt gerade den Gratis-Plan und kannst bis zu 5 Fänge eintragen. Danach freuen wir uns,
                wenn du auf ein Abo umsteigst.</p>
        </template>

        <div class="rounded-2xl border border-(--ink)/10 bg-white p-5">
            <p class="font-log text-[10px] uppercase tracking-[0.2em] text-(--ink)/50">Dein Start</p>
            <p class="mt-2 font-medium">Du kannst jetzt loslegen und deinen ersten Fang eintragen.</p>
        </div>

        <div class="mt-8">
            <VButton href="/dashboard" class="w-full">
                Weiter zum Dashboard
            </VButton>
        </div>
    </AuthenticationCard>
</template>
<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticationCard from '@/JetstreamComponents/AuthenticationCard.vue';
import VButton from '@/components/VButton.vue';
</script>
