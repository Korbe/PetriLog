<template>
    <PageWrapper title="Wiki herunterladen" eyebrow="Development · Seed-Daten" backTo="/admin/development">
        <p class="mb-5 flex items-start gap-2.5 rounded-2xl border border-dashed border-(--app-border-strong) px-4 py-3 text-sm text-(--app-muted) md:mb-6">
            <CommandLineIcon class="mt-0.5 size-5 shrink-0 text-(--app-accent-ink)" />
            <span>
                <strong class="text-(--app-ink)">Development-Funktion:</strong> Dieser Export dient dazu, den Wiki-Stand ins Repository zu
                übernehmen. Er ist <strong class="text-(--app-ink)">kein Backup</strong> – User, Fänge und Fotos sind nicht enthalten.
                Für Sicherungen gibt es die <Link href="/admin/development/backups" class="ui-link">Datenbank-Backups</Link>.
            </span>
        </p>

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">

            <!-- Komplettpaket -->
            <section class="ui-card flex flex-col p-5 md:p-7 lg:col-span-7">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                    <ArchiveBoxArrowDownIcon class="size-6" />
                </span>
                <h2 class="font-display mt-4 text-3xl">Komplettes Wiki herunterladen</h2>
                <p class="ui-muted mt-2 text-sm leading-relaxed">
                    Bundesländer, Vereine, Fischarten, Seen und Flüsse im Format der Seeder – inklusive Fischbildern und
                    Wappen. Vorschläge in Prüfung sind nicht enthalten.
                </p>

                <div class="mt-6">
                    <a v-if="zipAvailable" :href="route('admin.development.wiki-export.zip')"
                        class="inline-flex items-center gap-2 rounded-full bg-primary-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-primary-600">
                        <ArrowDownTrayIcon class="size-5" /> ZIP herunterladen
                    </a>
                    <p v-else class="flex items-start gap-2 rounded-2xl bg-[color-mix(in_srgb,var(--app-float)_12%,transparent)] px-4 py-3 text-sm">
                        <ExclamationTriangleIcon class="mt-0.5 size-5 shrink-0 text-(--app-float)" />
                        Auf dem Server fehlt die PHP-Erweiterung „zip“. Lade die Dateien einzeln herunter – oder nutze lokal
                        <code class="font-log text-xs">php artisan wiki:export</code>.
                    </p>
                </div>

                <div class="ui-panel mt-6 p-4 text-sm">
                    <p class="font-semibold">So kommt der Export ins Repo</p>
                    <ol class="mt-2 list-decimal space-y-1.5 pl-5 text-(--app-muted)">
                        <li>ZIP in den Ordner <code class="font-log text-xs text-(--app-ink)">database/seeders/</code> entpacken und vorhandene Dateien ersetzen.</li>
                        <li>Änderungen prüfen: <code class="font-log text-xs text-(--app-ink)">git diff --stat database/seeders</code></li>
                        <li>Committen – neue Installationen bekommen den Stand dann über <code class="font-log text-xs text-(--app-ink)">php artisan db:seed</code>.</li>
                    </ol>
                    <p class="mt-3 text-xs text-(--app-subtle)">
                        Die Reihenfolge der bestehenden Seed-Dateien bleibt erhalten – im Diff siehst du nur echte Änderungen.
                    </p>
                </div>
            </section>

            <!-- Einzeldateien -->
            <section class="ui-card overflow-hidden lg:col-span-5">
                <div class="px-5 pt-5 md:px-6 md:pt-6">
                    <p class="ui-kicker">Einzelne Dateien</p>
                    <p class="ui-muted mt-1 text-sm">Nur die JSON-Datei, ohne Bilder.</p>
                </div>
                <ul class="mt-3 divide-y divide-(--app-border)">
                    <li v-for="dataset in datasets" :key="dataset.name" class="flex items-center gap-3 px-5 py-3.5 md:px-6">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-(--app-surface-2) text-(--app-muted)">
                            <DocumentTextIcon class="size-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-log truncate text-sm text-(--app-ink)">{{ dataset.file }}</p>
                            <p class="text-xs text-(--app-muted)">{{ dataset.count }} {{ LABELS[dataset.name] }}</p>
                        </div>
                        <a :href="route('admin.development.wiki-export.dataset', dataset.name)" class="ui-icon-btn" :aria-label="`${dataset.file} herunterladen`"
                            title="Herunterladen">
                            <ArrowDownTrayIcon class="size-4" />
                        </a>
                    </li>
                </ul>
            </section>
        </div>
    </PageWrapper>
</template>

<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { Link } from '@inertiajs/vue3'
import {
    ArchiveBoxArrowDownIcon, ArrowDownTrayIcon, CommandLineIcon, DocumentTextIcon, ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    datasets: { type: Array, default: () => [] },
    zipAvailable: { type: Boolean, default: true },
})

const LABELS = {
    states: 'Bundesländer',
    associations: 'Vereine',
    fish: 'Fischarten',
    lakes: 'Seen',
    rivers: 'Flüsse',
}
</script>
