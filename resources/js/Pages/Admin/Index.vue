<template>
    <PageWrapper :title="'Hallo Admin ' + $page.props.auth.user.name" backTo="/dashboard">
        <div class="space-y-5">

            <!-- Grid für die Kacheln -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 w-full">

                <!-- Kacheln -->
                <div v-for="card in cards" :key="card.title"
                    class="bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
                    <h1 class="text-xl font-medium">{{ card.title }}</h1>
                    <p class="mt-2">{{ card.desc }}</p>
                    <VButton class="mt-5" :href="card.href">Zur Verwaltung</VButton>
                </div>

            </div>

        </div>
    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';

const cards = [
    {
        title: 'Pulse',
        desc: 'Laravel Pulse überwacht deine App in Echtzeit und liefert sofortige Einblicke in Performance, Queries und Fehler.',
        href: '/pulse',
    },
    {
        title: 'Fehlerberichte',
        desc: 'Hier kannst du alle gemeldeten Fehler einsehen und prüfen, welche Probleme aufgetreten sind.',
        href: '/admin/bugreports',
    },
    {
        title: 'Fish Verwaltung',
        desc: 'Hier können alle Fische verwaltet werden.',
        href: '/admin/fish',
    },
    {
        title: 'Bundesland Verwaltung',
        desc: 'Hier können alle Bundesländer verwaltet werden.',
        href: '/admin/state',
    },
    {
        title: 'See Verwaltung',
        desc: 'Hier können alle Seen verwaltet werden.',
        href: '/admin/lake',
    },
    {
        title: 'Fluss Verwaltung',
        desc: 'Hier können alle Flüsse verwaltet werden.',
        href: '/admin/river',
    },
];
</script>