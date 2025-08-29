<template>
    <PageWrapper :title="'Hallo Admin ' + $page.props.auth.user.name" backTo="/dashboard">
        <div class="space-y-5">

            <div class="flex space-x-5 w-full">

                <div class="flex flex-wrap lg:flex-nowrap w-full gap-5">

                    <div
                        class="w-full lg:w-1/3 bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
                        <h1 class="text-xl font-medium">Pulse</h1>
                        <p>Laravel Pulse überwacht deine App in Echtzeit und liefert sofortige Einblicke in Performance,
                            Queries und Fehler.</p>
                        <a class="m-5 cursor-pointer btn bg-primary-500 text-gray-100 hover:bg-primary-600 active:bg-primary-600 transition-all duration-150 dark:bg-primary-500 dark:text-gray-100 dark:hover:bg-primary-600 dark:active:bg-primary-600" href="/pulse">Zu Pulse</a>
                    </div>

                     <div
                        class="w-full lg:w-1/3 bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">
                        <h1 class="text-xl font-medium">Fehlerberichte</h1>
                        <p>Hier kannst du alle gemeldeten Fehler einsehen und prüfen, welche Probleme aufgetreten sind.</p>
                        <VButton class="m-5" href="/admin/bugreports">Zu Fehlerberichte</VButton>
                    </div>

                </div>

            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
</script>