<template>
    <PageWrapper :title="`Hallo, ${$page.props.auth.user.name}`" eyebrow="Administration" backTo="/dashboard">
        <template v-slot:actions>
            <div class="flex gap-1 rounded-full bg-(--app-surface-2) p-1" role="radiogroup" aria-label="Zeitraum">
                <button v-for="option in periods" :key="option.days" type="button" role="radio" :aria-checked="stats.period === option.days"
                    class="rounded-full px-3.5 py-1.5 text-sm font-semibold transition"
                    :class="stats.period === option.days ? 'bg-(--app-surface) text-(--app-ink) shadow-(--app-shadow)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                    @click="setPeriod(option.days)">
                    {{ option.label }}
                </button>
            </div>
        </template>

        <div class="space-y-6 md:space-y-8" :class="{ 'opacity-60 transition-opacity': loading }">

            <!-- Hinweise -->
            <div v-if="pendingSubmissions || openCorrections || queueWarning" class="grid gap-3 md:grid-cols-2">
                <Link v-if="pendingSubmissions" href="/admin/submissions"
                    class="ui-card ui-card-hover group flex items-center gap-4 border-(--app-float)! p-4">
                    <span class="font-display flex size-11 shrink-0 items-center justify-center rounded-2xl bg-(--app-float) text-xl text-white">
                        {{ pendingSubmissions }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ pendingSubmissions === 1 ? 'Neuer Vorschlag' : 'Neue Vorschläge' }} zur Prüfung</span>
                        <span class="ui-muted block text-sm">Fischarten und Gewässer freigeben oder ablehnen.</span>
                    </span>
                    <ArrowRightIcon class="size-5 shrink-0 text-(--app-accent-ink) transition group-hover:translate-x-0.5" />
                </Link>
                <Link v-if="openCorrections" href="/admin/corrections"
                    class="ui-card ui-card-hover group flex items-center gap-4 border-(--app-accent)! p-4">
                    <span class="font-display flex size-11 shrink-0 items-center justify-center rounded-2xl bg-(--app-accent) text-xl text-white">
                        {{ openCorrections }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ openCorrections === 1 ? 'Offene Fehlermeldung' : 'Offene Fehlermeldungen' }}</span>
                        <span class="ui-muted block text-sm">Hinweise von Usern zu Fischen und Gewässern.</span>
                    </span>
                    <ArrowRightIcon class="size-5 shrink-0 text-(--app-accent-ink) transition group-hover:translate-x-0.5" />
                </Link>
                <div v-if="queueWarning" class="flex items-start gap-3 rounded-[1.25rem] bg-(--app-danger-soft) p-4 text-sm" role="alert">
                    <ExclamationTriangleIcon class="mt-0.5 size-5 shrink-0 text-(--app-danger)" />
                    <p>
                        <strong class="text-(--app-danger)">Queue prüfen:</strong> {{ queueWarning }}
                        Mails, Wetterdaten und Bildoptimierung laufen über die Queue.
                    </p>
                </div>
            </div>

            <!-- KPIs -->
            <section aria-label="Kennzahlen">
                <p class="ui-kicker mb-3">{{ rangeLabel }}</p>
                <dl class="grid grid-cols-2 gap-3 md:gap-4 xl:grid-cols-4">
                    <div v-for="kpi in kpis" :key="kpi.key" class="ui-card p-4 md:p-5">
                        <dt class="flex items-center gap-2 text-xs font-medium text-(--app-muted)">
                            <component :is="kpi.icon" class="size-4 text-(--app-subtle)" /> {{ kpi.label }}
                        </dt>
                        <dd class="font-display mt-2 text-3xl leading-none md:text-4xl">{{ format(kpi.value) }}</dd>
                        <dd class="mt-2 text-xs" :class="kpi.delta.tone">{{ kpi.delta.label }}</dd>
                    </div>
                </dl>
                <p v-if="!stats.health.trackingSince" class="ui-muted mt-3 text-xs">
                    Die Traffic- und Mail-Statistik zählt ab jetzt – ältere Werte gibt es dafür nicht.
                </p>
            </section>

            <!-- Verlauf + Bestand -->
            <div class="grid gap-5 md:gap-6 lg:grid-cols-12">
                <section class="ui-card p-5 md:p-6 lg:col-span-8">
                    <StatsTrendChart :series="stats.series" />
                </section>

                <section class="ui-card p-5 md:p-6 lg:col-span-4">
                    <p class="ui-kicker">Gesamtbestand</p>
                    <dl class="mt-3 divide-y divide-(--app-border)">
                        <div v-for="row in totals" :key="row.label" class="flex items-center justify-between gap-3 py-2.5 text-sm">
                            <dt class="text-(--app-muted)">{{ row.label }}</dt>
                            <dd class="font-log text-(--app-ink)">{{ row.value }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <!-- Mails -->
            <div class="grid gap-5 md:gap-6 lg:grid-cols-12">
                <section class="ui-card p-5 md:p-6 lg:col-span-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="ui-kicker">Mails nach Art</p>
                        <span class="ui-badge">{{ format(stats.kpis.mails.value) }} gesamt</span>
                    </div>
                    <ul v-if="stats.mailsByType.length" class="mt-4 space-y-3">
                        <li v-for="mail in stats.mailsByType" :key="mail.type">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="truncate font-medium">{{ mail.label }}</span>
                                <span class="font-log shrink-0 text-(--app-muted)">{{ format(mail.total) }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-(--app-surface-2)">
                                <div class="h-full rounded-full bg-(--app-accent)" :style="{ width: share(mail.total, maxMailType) }"></div>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="ui-panel mt-4 px-4 py-5 text-sm text-(--app-muted)">In diesem Zeitraum wurden keine Mails verschickt.</p>
                </section>

                <section class="ui-card overflow-hidden lg:col-span-7">
                    <p class="ui-kicker px-5 pt-5 md:px-6 md:pt-6">Zuletzt verschickt</p>
                    <ul v-if="stats.recentMails.length" class="mt-3 divide-y divide-(--app-border)">
                        <li v-for="mail in stats.recentMails" :key="mail.id" class="flex items-start gap-3 px-5 py-3 md:px-6">
                            <EnvelopeIcon class="mt-0.5 size-4 shrink-0 text-(--app-subtle)" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ mail.subject || mail.label }}</p>
                                <p class="truncate text-xs text-(--app-muted)">{{ mail.label }} · an {{ mail.recipient }}</p>
                            </div>
                            <time class="font-log shrink-0 text-xs text-(--app-subtle)" :datetime="mail.sent_at">{{ relative(mail.sent_at) }}</time>
                        </li>
                    </ul>
                    <p v-else class="ui-panel mx-5 mb-5 mt-3 px-4 py-5 text-sm text-(--app-muted) md:mx-6 md:mb-6">Noch keine Mails protokolliert.</p>
                </section>
            </div>

            <!-- Traffic-Details & Fänge -->
            <div class="grid gap-5 md:grid-cols-2 md:gap-6 xl:grid-cols-4">
                <section class="ui-card p-5 md:p-6">
                    <p class="ui-kicker">Geräte</p>
                    <p class="ui-muted mt-1 text-xs">Besucher nach Gerät – ohne Cookies, alle Besucher.</p>
                    <template v-if="stats.devices.total">
                        <div class="mt-4 flex h-3 overflow-hidden rounded-full bg-(--app-surface-2)" role="img"
                            :aria-label="deviceRows.map(d => `${d.label} ${d.percent} %`).join(', ')">
                            <div v-for="d in deviceRows" :key="d.key" :class="d.bar" :style="{ width: `${d.percent}%` }"></div>
                        </div>
                        <ul class="mt-4 space-y-2.5">
                            <li v-for="d in deviceRows" :key="d.key" class="flex items-center gap-2.5 text-sm">
                                <component :is="d.icon" class="size-4 shrink-0" :class="d.text" />
                                <span class="flex-1 font-medium">{{ d.label }}</span>
                                <span class="font-log text-(--app-muted)">{{ format(d.visitors) }}</span>
                                <span class="font-log w-11 text-right font-semibold">{{ d.percent }} %</span>
                            </li>
                        </ul>
                        <p class="mt-4 flex items-center gap-2 border-t border-(--app-border) pt-3 text-xs text-(--app-muted)">
                            <BoltIcon class="size-4 shrink-0 text-(--app-float)" />
                            {{ appShare }} % nutzen PetriLog als installierte App
                        </p>
                    </template>
                    <p v-else class="ui-panel mt-4 px-4 py-5 text-sm text-(--app-muted)">Noch keine Gerätedaten – werden ab jetzt erfasst.</p>
                </section>

                <section class="ui-card p-5 md:p-6">
                    <p class="ui-kicker">Meistbesuchte Seiten</p>
                    <ol v-if="stats.topPages.length" class="mt-4 space-y-3">
                        <li v-for="page in stats.topPages" :key="page.page">
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="truncate font-medium">{{ page.page }}</span>
                                <span class="font-log shrink-0 text-(--app-muted)">{{ format(page.views) }}</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-(--app-surface-2)">
                                <div class="h-full rounded-full bg-(--app-accent)" :style="{ width: share(page.views, stats.topPages[0].views) }"></div>
                            </div>
                        </li>
                    </ol>
                    <p v-else class="ui-panel mt-4 px-4 py-5 text-sm text-(--app-muted)">Noch keine Aufrufe erfasst.</p>
                </section>

                <section class="ui-card p-5 md:p-6">
                    <p class="ui-kicker">Herkunft</p>
                    <p class="ui-muted mt-1 text-xs">Externe Seiten, über die Besucher gekommen sind.</p>
                    <ol v-if="stats.topReferrers.length" class="mt-4 space-y-2.5">
                        <li v-for="ref in stats.topReferrers" :key="ref.host" class="flex items-center justify-between gap-3 text-sm">
                            <span class="flex min-w-0 items-center gap-2">
                                <GlobeAltIcon class="size-4 shrink-0 text-(--app-subtle)" />
                                <span class="truncate font-medium">{{ ref.host }}</span>
                            </span>
                            <span class="font-log shrink-0 text-(--app-muted)">{{ format(ref.views) }}</span>
                        </li>
                    </ol>
                    <p v-else class="ui-panel mt-4 px-4 py-5 text-sm text-(--app-muted)">Bisher nur direkte Aufrufe.</p>
                </section>

                <section class="ui-card p-5 md:p-6">
                    <p class="ui-kicker">Meistgefangen</p>
                    <ol v-if="stats.topFish.length" class="mt-4 space-y-2.5">
                        <li v-for="(fish, index) in stats.topFish" :key="fish.name" class="flex items-center gap-3 text-sm">
                            <span class="font-display w-5 shrink-0 text-lg text-(--app-float)">{{ index + 1 }}</span>
                            <span class="min-w-0 flex-1 truncate font-medium">{{ fish.name }}</span>
                            <span class="font-log shrink-0 text-(--app-muted)">{{ format(fish.total) }}×</span>
                        </li>
                    </ol>
                    <p v-else class="ui-panel mt-4 px-4 py-5 text-sm text-(--app-muted)">Keine neuen Fänge in diesem Zeitraum.</p>
                </section>
            </div>

            <!-- Verwaltung -->
            <section v-for="group in groups" :key="group.title">
                <p class="ui-kicker mb-3">{{ group.title }}</p>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                    <component :is="card.external ? 'a' : Link" v-for="card in group.cards" :key="card.title"
                        :href="card.href" :target="card.external ? '_blank' : undefined"
                        :rel="card.external ? 'noopener' : undefined"
                        class="ui-card ui-card-hover group flex items-center gap-3.5 p-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-2xl" :class="card.tone">
                            <component :is="card.icon" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-1.5 font-semibold">
                                {{ card.title }}
                                <ArrowTopRightOnSquareIcon v-if="card.external" class="size-3.5 text-(--app-subtle)" />
                            </span>
                            <span class="ui-muted block truncate text-xs">{{ card.desc }}</span>
                        </span>
                    </component>
                </div>
            </section>

        </div>
    </PageWrapper>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import StatsTrendChart from './partials/StatsTrendChart.vue';
import {
    ArrowRightIcon, ArrowTopRightOnSquareIcon, ChatBubbleLeftRightIcon, UsersIcon, EnvelopeIcon, MapIcon,
    BeakerIcon, UserGroupIcon, ChartBarIcon, InboxArrowDownIcon, ExclamationTriangleIcon, EyeIcon, UserIcon,
    UserPlusIcon, BookOpenIcon, PhotoIcon, StarIcon, GlobeAltIcon, BoltIcon, CommandLineIcon,
    DevicePhoneMobileIcon, DeviceTabletIcon, ComputerDesktopIcon, FlagIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: { type: Object, required: true },
    pendingSubmissions: { type: Number, default: 0 },
    openCorrections: { type: Number, default: 0 },
});

/* Zeitraum */
const periods = [
    { days: 7, label: '7 T' },
    { days: 30, label: '30 T' },
    { days: 90, label: '90 T' },
    { days: 365, label: '1 J' },
];
const loading = ref(false);

function setPeriod(days) {
    router.get('/admin', { period: days }, {
        only: ['stats'], preserveScroll: true, preserveState: true, replace: true,
        onStart: () => { loading.value = true },
        onFinish: () => { loading.value = false },
    });
}

const dateFormat = (date) => new Date(`${date}T12:00:00`).toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit', year: 'numeric' });
const rangeLabel = computed(() => `${dateFormat(props.stats.range.from)} – ${dateFormat(props.stats.range.to)}`);

/* Formatierung */
const format = (value) => Number(value ?? 0).toLocaleString('de-AT');
/* Geräte */
const DEVICE_STYLE = {
    mobile: { icon: DevicePhoneMobileIcon, bar: 'bg-(--app-accent)', text: 'text-(--app-accent)' },
    tablet: { icon: DeviceTabletIcon, bar: 'bg-(--app-float)', text: 'text-(--app-float)' },
    desktop: { icon: ComputerDesktopIcon, bar: 'bg-(--app-ink)', text: 'text-(--app-ink)' },
};
const percentOf = (value, total) => (total ? Math.round((value / total) * 100) : 0);
const deviceRows = computed(() => props.stats.devices.rows.map(d => ({
    ...d,
    ...DEVICE_STYLE[d.key],
    percent: percentOf(d.visitors, props.stats.devices.total),
})));
const appShare = computed(() => percentOf(props.stats.devices.appVisitors, props.stats.devices.total));

const share = (value, max) => `${max ? Math.max(4, Math.round((value / max) * 100)) : 0}%`;

const relative = (date) => {
    const minutes = Math.round((Date.now() - new Date(date).getTime()) / 60000);
    if (minutes < 1) return 'gerade eben';
    if (minutes < 60) return `vor ${minutes} Min.`;
    if (minutes < 60 * 24) return `vor ${Math.round(minutes / 60)} Std.`;
    return new Date(date).toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit' });
};

function delta({ value, previous }) {
    if (!previous && !value) return { label: 'keine Daten', tone: 'text-(--app-subtle)' };
    if (!previous) return { label: 'neu im Zeitraum', tone: 'text-(--app-success)' };
    const change = Math.round(((value - previous) / previous) * 100);
    if (change === 0) return { label: '± 0 % zum Vorzeitraum', tone: 'text-(--app-muted)' };
    return change > 0
        ? { label: `▲ ${change} % zum Vorzeitraum`, tone: 'text-(--app-success)' }
        : { label: `▼ ${Math.abs(change)} % zum Vorzeitraum`, tone: 'text-(--app-danger)' };
}

const KPI_META = [
    { key: 'views', label: 'Seitenaufrufe', icon: EyeIcon },
    { key: 'visitors', label: 'Besucher', icon: UserIcon },
    { key: 'users', label: 'Neue User', icon: UserPlusIcon },
    { key: 'subscriptions', label: 'Neue Abos', icon: StarIcon },
    { key: 'catches', label: 'Neue Fänge', icon: BookOpenIcon },
    { key: 'photos', label: 'Hochgeladene Fotos', icon: PhotoIcon },
    { key: 'activeUsers', label: 'Aktive Angler', icon: BoltIcon },
    { key: 'mails', label: 'Verschickte Mails', icon: EnvelopeIcon },
];
const kpis = computed(() => KPI_META.map(meta => ({
    ...meta,
    value: props.stats.kpis[meta.key].value,
    delta: delta(props.stats.kpis[meta.key]),
})));

const totals = computed(() => {
    const t = props.stats.totals;
    return [
        { label: 'User gesamt', value: format(t.users) },
        { label: 'davon bestätigt', value: format(t.verifiedUsers) },
        { label: 'Aktive Abos', value: format(t.subscribers) },
        { label: 'Fänge gesamt', value: format(t.catches) },
        { label: 'Gespeicherte Spots', value: format(t.spots) },
        { label: 'Tickets', value: format(t.tickets) },
        { label: 'Wartende Jobs (Queue)', value: format(props.stats.health.queuedJobs) },
        { label: 'Fehlgeschlagene Jobs', value: format(props.stats.health.failedJobs) },
    ];
});

const maxMailType = computed(() => Math.max(0, ...props.stats.mailsByType.map(m => m.total)));

// Hängen Jobs länger als 15 Minuten, läuft vermutlich kein Queue-Worker
const queueWarning = computed(() => {
    const h = props.stats.health;
    const parts = [];
    if (h.queuedJobs && h.oldestJobMinutes > 15) parts.push(`${format(h.queuedJobs)} Jobs warten, der älteste seit ${h.oldestJobMinutes > 1440 ? `${Math.round(h.oldestJobMinutes / 1440)} Tagen` : `${h.oldestJobMinutes} Minuten`} – läuft der Queue-Worker?`);
    if (h.failedJobsRecent) parts.push(`${h.failedJobsRecent} Jobs sind in den letzten 24 Std. fehlgeschlagen.`);
    return parts.join(' ');
});

/* Verwaltung */
const accent = 'bg-(--app-accent-soft) text-(--app-accent-ink)';
const green = 'bg-(--app-success-soft) text-(--app-success)';
const orange = 'bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-(--app-float)';

const groups = [
    {
        title: 'Betrieb',
        cards: [
            { title: 'Vorschläge', desc: 'Fischarten & Gewässer prüfen', href: '/admin/submissions', icon: InboxArrowDownIcon, tone: orange },
            { title: 'Fehlermeldungen', desc: 'Hinweise zu Wiki-Einträgen', href: '/admin/corrections', icon: FlagIcon, tone: orange },
            { title: 'Tickets', desc: 'Probleme & Wünsche', href: '/admin/tickets', icon: ChatBubbleLeftRightIcon, tone: orange },
            { title: 'User', desc: 'Abo-Status & Impersonieren', href: '/admin/user', icon: UsersIcon, tone: accent },
            { title: 'Newsletter', desc: 'Erstellen, testen, versenden', href: '/admin/newsletter', icon: EnvelopeIcon, tone: accent },
            // Nur Admins – Editoren sehen die Karte nicht
            ...(usePage().props.auth?.user?.is_admin
                ? [{ title: 'Development', desc: 'Backups, Wiki-Export, Pulse', href: '/admin/development', icon: CommandLineIcon, tone: green }]
                : []),
        ],
    },
    {
        title: 'Wiki-Inhalte',
        cards: [
            { title: 'Bundesländer', desc: 'Wappen & Beschreibung', href: '/admin/state', icon: MapIcon, tone: green },
            { title: 'Seen', desc: 'Fische, Tipps, Kartenverkauf', href: '/admin/lake', icon: MapIcon, tone: accent },
            { title: 'Flüsse', desc: 'Fische, Tipps, Kartenverkauf', href: '/admin/river', icon: MapIcon, tone: accent },
            { title: 'Fische', desc: 'Bild & Steckbrief', href: '/admin/fish', icon: BeakerIcon, tone: orange },
            { title: 'Vereine', desc: 'Link & Beschreibung', href: '/admin/association', icon: UserGroupIcon, tone: green },
        ],
    },
];
</script>
