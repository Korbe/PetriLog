<template>
    <PageWrapper :title="'Hallo Admin ' + $page.props.auth.user.name" backTo="/dashboard">
        <div class="space-y-5">

            <!-- <VButton @click="sendMail">
                Mail senden
            </VButton> -->

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 w-full">

                <div class="bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
                    <h1 class="text-xl font-medium">Pulse</h1>
                    <p class="mt-2">Laravel Pulse überwacht deine App in Echtzeit und liefert sofortige Einblicke in
                        Performance, Queries und Fehler.</p>
                    <a class="mt-5 text-center rounded-lg px-4 py-2 cursor-pointer bg-primary-500 text-gray-100 shadow-sm hover:bg-primary-600 hover:shadow active:bg-primary-700 active:shadow-inner active:scale-[0.97] focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-150 dark:bg-primary-500 dark:text-gray-100 dark:hover:bg-primary-600 dark:active:bg-primary-700"
                        href="/pulse" target="_blank">Zur Verwaltung</a>
                </div>

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
import { Inertia } from '@inertiajs/inertia';

// const sendMail = () => {
//     Inertia.post('/send-mail');
// };

const cards = [
    {
        title: 'Tickets',
        desc: 'Hier kannst du alle gemeldeten Tickets einsehen und prüfen, welche Probleme aufgetreten sind.',
        href: '/admin/tickets',
    },
    {
        title: 'User Verwaltung',
        desc: 'Hier können alle User angesehen werden.',
        href: '/admin/user',
    },
    {
        title: 'Newsletter Verwaltung',
        desc: 'Hier können Newsletter erstellt und versendet werden.',
        href: '/admin/newsletter',
    },
    {
        title: 'Bundesland Verwaltung',
        desc: 'Hier können alle Bundesländer verwaltet werden.',
        href: '/admin/state',
    },
    {
        title: 'Fisch Verwaltung',
        desc: 'Hier können alle Fische verwaltet werden.',
        href: '/admin/fish',
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
    {
        title: 'Verein Verwaltung',
        desc: 'Hier können alle Vereine verwaltet werden.',
        href: '/admin/association',
    },
    
];
</script>