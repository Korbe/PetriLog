<script setup>
const features = [
    'Fischart, Länge, Gewicht & Köder',
    'Tiefe, Wassertemperatur & Luftdruck',
    'Bis zu 3 Fotos pro Fang',
    'GPS-Spot mit Google & Apple Maps',
    'Monats- & Jahresstatistik',
    'Fänge mit Freunden teilen',
    'Smartphone, Tablet & PC',
];
</script>

<template>
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
        <div class="grid gap-5 lg:grid-cols-12">

            <!-- Free -->
            <article
                class="ph-reveal flex flex-col rounded-[2rem] bg-white p-8 ring-1 ring-(--ink)/10 md:p-10 lg:col-span-5">
                <p class="font-log text-[11px] uppercase tracking-[0.2em] text-(--ink)/50">Zum Reinschnuppern</p>
                <h2 class="font-display mt-3 text-4xl">Kostenlos</h2>
                <p class="mt-6 flex items-baseline gap-2">
                    <span class="font-display text-7xl leading-none">0 €</span>
                </p>
                <p class="mt-3 text-(--ink)/65">Alle Funktionen, bis zu <strong class="text-(--ink)">5 Fänge</strong>
                    in deinem Fangbuch.</p>

                <ul class="mt-8 space-y-3 border-t border-(--ink)/10 pt-8 text-(--ink)/75">
                    <li class="flex gap-3">
                        <span class="mt-2.5 h-px w-3 shrink-0 bg-(--ink)/40"></span>Alle Fangdaten & Fotos
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2.5 h-px w-3 shrink-0 bg-(--ink)/40"></span>Statistik & Gewässer-Wiki
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-2.5 h-px w-3 shrink-0 bg-(--ink)/40"></span>Jederzeit auf Plus upgraden
                    </li>
                </ul>

                <div class="mt-auto pt-10">
                    <Link href="/register"
                        class="flex items-center justify-center rounded-full border border-(--ink)/20 px-6 py-4 text-base font-semibold transition hover:border-(--ink)">
                        Kostenlos registrieren
                    </Link>
                </div>
            </article>

            <!-- Plus -->
            <article
                class="ph-reveal ph-grain relative isolate flex flex-col overflow-hidden rounded-[2rem] bg-(--lake-900) p-8 text-(--paper) shadow-[0_40px_80px_-40px_rgba(3,20,26,0.7)] md:p-10 lg:col-span-7"
                style="transition-delay: 100ms">
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_70%_60%_at_100%_0%,rgba(17,141,240,0.35),transparent_70%)]"
                    aria-hidden="true"></div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-log text-[11px] uppercase tracking-[0.2em] text-primary-300">Für die ganze Saison</p>
                        <h2 class="font-display mt-3 text-4xl">PetriLog Plus</h2>
                    </div>
                    <span
                        class="font-log rounded-full bg-(--float) px-3 py-1 text-[11px] uppercase tracking-[0.15em] text-white">Unbegrenzt</span>
                </div>

                <p class="mt-6 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <span class="font-display text-7xl leading-none">49,99 €</span>
                    <span class="text-(--paper)/65">pro Jahr inkl. MwSt.</span>
                </p>
                <p class="font-log mt-3 text-xs text-(--paper)/50">entspricht ca. 4,17 € im Monat</p>

                <ul class="mt-8 grid gap-x-8 gap-y-3 border-t border-white/10 pt-8 sm:grid-cols-2">
                    <li class="flex gap-3 font-semibold sm:col-span-2">
                        <svg class="mt-1 size-4 shrink-0 text-(--float)" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="m2.5 8.5 3.5 3.5 7.5-8" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Unbegrenzt viele Fänge
                    </li>
                    <li v-for="feature in features" :key="feature" class="flex gap-3 text-(--paper)/80">
                        <svg class="mt-1 size-4 shrink-0 text-primary-300" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                            <path d="m2.5 8.5 3.5 3.5 7.5-8" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ feature }}
                    </li>
                </ul>

                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
                    <Link href="/register"
                        class="group inline-flex shrink-0 items-center justify-center gap-3 whitespace-nowrap rounded-full bg-(--paper) px-7 py-4 text-base font-semibold text-(--ink) transition hover:bg-white">
                        Jetzt starten
                        <svg class="size-4 transition-transform group-hover:translate-x-1" viewBox="0 0 16 16" fill="none"
                            aria-hidden="true">
                            <path d="M1 8h13m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </Link>
                    <p class="text-sm text-(--paper)/55">Registrieren, Fänge eintragen und im Dashboard unter „Abo“
                        upgraden.</p>
                </div>
            </article>

        </div>
    </section>
</template>
