<template>
    <Layout>

        <Head title="Kontakt" />

        <PageIllustration />

        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <!-- Hero content -->
            <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                <!-- Section header -->
                <div class="text-center pb-12">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6">
                        Kontakt</h1>
                    <div class="max-w-3xl mx-auto">
                        <p class="text-lg text-gray-700">Sie erreichen uns jederzeit per Mail oder persönlich per
                            Telefon</p>
                    </div>
                </div>

                <div class="max-w-lg mx-auto gap-8">
                    <div
                        class="relative flex flex-col h-full p-5 rounded-2xl backdrop-blur-xs bg-linear-to-tr from-gray-900 to-gray-700 shadow-lg shadow-black/[0.03]">
                        <div class="text-center">
                            <div class="text-lg text-gray-300"><b>Email</b>: <a
                                    href="mailto:info@petrilog.com">info@petrilog.com</a></div>
                            <div class="text-lg text-gray-300"><b>Perschon (Vetrieb)</b>: <a href="tel:+436505222753">+43 650 522 27 53</a></div>
                            <div class="text-lg text-gray-300"><b>Korbitsch (Technische Leitung)</b>: <a href="tel:+436701923388">+43 670 192 33 88</a></div>
                        </div>
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

</script>