<script setup>
import { ref } from 'vue';
import PageIllustration from '../../PageIllustration.vue';
import { Link } from '@inertiajs/vue3';

const isAnnual = ref(true)
</script>
<template>
    <section class="relative">

        <PageIllustration />

        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            <!-- Hero content -->
            <div class="pt-32 pb-12 md:pt-10 md:pb-20">

                <!-- Section header -->
                <div class="text-center pb-12">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6">
                        Ein Jahresabo für echte Angler</h1>
                    <div class="max-w-3xl mx-auto">
                        <p class="text-lg text-gray-700">Alle Funktionen freigeschaltet, um deine Fänge das ganze Jahr
                            über zu dokumentieren und zu teilen.</p>
                    </div>
                </div>

                <!-- Pricing tables -->
                <div>

                    <!-- Pricing toggle -->
                    <div class="flex justify-center max-w-xs m-auto mb-16">
                        <div class="relative flex w-full mx-6 p-1 bg-gray-200 rounded-lg">
                            <span class="absolute inset-0 m-1 pointer-events-none" aria-hidden="true">
                                <span class="absolute inset-0 w-1/2 bg-white rounded-sm shadow-sm transform transition"
                                    :class="isAnnual ? 'translate-x-0' : 'translate-x-full'"></span>
                            </span>
                            <button class="relative flex-1 text-sm font-medium p-1 transition"
                                :class="isAnnual ? '' : 'text-gray-500'" @click="isAnnual = true"
                                :aria-pressed="isAnnual">
                                Jährlich
                            </button>
                            <button class="relative flex-1 text-sm font-medium p-1 transition"
                                :class="isAnnual ? 'text-gray-500' : ''" @click="isAnnual = false"
                                :aria-pressed="!isAnnual">
                                Monatlich
                            </button>
                        </div>
                    </div>

                    <div class="max-w-sm mx-auto gap-8">
                        <div
                            class="relative flex flex-col h-full p-5 rounded-2xl backdrop-blur-xs bg-linear-to-tr from-gray-900 to-gray-700 shadow-lg shadow-black/[0.03]">
                            <div class="mb-4">
                                <div
                                    class="text-gray-200 font-medium underline underline-offset-4 decoration-gray-600 mb-1">
                                    Jahresabo</div>
                                <div class="flex items-baseline pb-4 mb-4 border-b border-gray-600 border-dashed">
                                    <span class="text-gray-200 text-2xl font-bold">$</span>
                                    <span class="text-gray-200 text-4xl font-bold tabular-nums"
                                        v-text="isAnnual ? 60 : 5"></span>
                                    <span class="text-sm text-gray-400 pl-1"
                                        v-text="isAnnual ? '/Jahr' : '/Monat'"></span>
                                </div>
                                <div class="text-sm text-gray-300">Ein Abo für alles</div>
                            </div>
                            <ul class="text-sm text-gray-400 space-y-2 grow">
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Unlimitierte Einträge</span>
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Bis zu 3 Bildern pro Fang</span>
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Statistiken</span>
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Standort merken</span>
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Erfolge teilen</span>
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Mobile & Desktop Nutzung</span>
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-3 h-3 fill-current text-emerald-500 mr-2 shrink-0" viewBox="0 0 12 12"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M10.28 2.28L3.989 8.575 1.695 6.28A1 1 0 00.28 7.695l3 3a1 1 0 001.414 0l7-7A1 1 0 0010.28 2.28z" />
                                    </svg>
                                    <span>Cloud-Speicherung</span>
                                </li>
                            </ul>
                            <div class="mt-6">
                                <Link class="btn-sm py-1.5 rounded-lg text-white bg-linear-to-t from-blue-600 to-blue-500 bg-[length:100%_100%] hover:bg-[length:100%_150%] bg-[bottom] shadow-sm w-full"
                                    href="register">Jetzt kostenlos testen</Link>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </section>
</template>