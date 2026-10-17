<template>
    <PageWrapper title="Development" eyebrow="Administration · Werkzeuge" backTo="/admin">
        <p class="ui-muted mb-6 max-w-2xl text-sm">
            Entwicklungs-, Wartungs- und Administrationswerkzeuge. Nur für Admins sichtbar – Aktionen hier betreffen den
            gesamten Server.
        </p>

        <div class="space-y-8">
            <section v-for="group in groups" :key="group.title">
                <p class="ui-kicker mb-3">{{ group.title }}</p>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <component :is="tool.external ? 'a' : Link" v-for="tool in group.tools" :key="tool.key" :href="tool.href"
                        :target="tool.external ? '_blank' : undefined" :rel="tool.external ? 'noopener' : undefined"
                        class="ui-card ui-card-hover group flex flex-col p-5 md:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                                <component :is="ICONS[tool.icon] ?? WrenchScrewdriverIcon" class="size-6" />
                            </span>
                            <span v-if="tool.badge" class="ui-badge">{{ tool.badge }}</span>
                            <ArrowTopRightOnSquareIcon v-else-if="tool.external" class="size-4 text-(--app-subtle)" />
                        </div>
                        <h2 class="mt-4 text-lg font-semibold">{{ tool.title }}</h2>
                        <p class="ui-muted mt-1 flex-1 text-sm">{{ tool.desc }}</p>

                        <div v-if="tool.status" class="mt-4 flex items-center gap-2 rounded-xl px-3 py-2 text-xs font-medium" :class="TONES[tool.status.tone]">
                            <span class="size-2 shrink-0 rounded-full bg-current"></span>
                            <span class="truncate">{{ tool.status.label }}</span>
                            <span class="ml-auto shrink-0 opacity-70">{{ tool.status.detail }}</span>
                        </div>

                        <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-(--app-accent-ink)">
                            Öffnen <ArrowRightIcon class="size-4 transition group-hover:translate-x-0.5" />
                        </span>
                    </component>
                </div>
            </section>
        </div>
    </PageWrapper>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import {
    ArrowDownTrayIcon, ArrowRightIcon, ArrowTopRightOnSquareIcon, ChartBarIcon, CircleStackIcon, WrenchScrewdriverIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    groups: { type: Array, default: () => [] },
})

// Icon-Namen kommen vom Server (DevelopmentController::tools) – neue Werkzeuge hier ergänzen
const ICONS = {
    backup: CircleStackIcon,
    download: ArrowDownTrayIcon,
    chart: ChartBarIcon,
}

const TONES = {
    success: 'bg-(--app-success-soft) text-(--app-success)',
    warning: 'bg-[color-mix(in_srgb,var(--app-float)_12%,transparent)] text-(--app-float)',
    danger: 'bg-(--app-danger-soft) text-(--app-danger)',
}
</script>
