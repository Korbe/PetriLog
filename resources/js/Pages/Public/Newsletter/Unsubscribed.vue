<template>
    <Layout>

        <Head title="Newsletter abbestellt" />

        <PageIllustration />

        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <!-- Hero content -->
            <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                <!-- Section header -->
                <div class="text-center pb-12">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6">
                        Newsletter abbestellt</h1>
                    <div class="max-w-3xl mx-auto">
                        <p class="text-lg text-gray-700">Schade, dass du unseren Newsletter abbestellt hast. Du wurdest
                            erfolgreich abgemeldet.</p>
                        <p class="text-lg text-gray-700">Du kannst dich jederzeit wieder in deinem Profil anmelden, um
                            Neuigkeiten zu erhalten.</p>
                    </div>
                </div>
            </div>
        </div>

    </Layout>
</template>
<script setup>
import Layout from '@/Layouts/Public/Layout.vue';
import PageIllustration from '../PageIllustration.vue';
import { Head } from '@inertiajs/vue3';
import VButton from '@/components/VButton.vue';
</script>