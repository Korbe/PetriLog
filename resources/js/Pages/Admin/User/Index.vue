<template>
    <PageWrapper title="User-Verwaltung" eyebrow="Administration" backTo="/admin">
        <div class="space-y-5">

            <!-- Segmente -->
            <div class="no-scrollbar -mx-1 flex gap-2 overflow-x-auto px-1 pb-1" role="tablist" aria-label="User filtern">
                <button v-for="segment in segments" :key="segment.key" type="button" role="tab" :aria-selected="filter === segment.key"
                    class="flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition"
                    :class="filter === segment.key
                        ? 'border-(--app-ink) bg-(--app-ink) text-(--app-surface)'
                        : 'border-(--app-border) bg-(--app-surface) text-(--app-muted) hover:border-(--app-border-strong) hover:text-(--app-ink)'"
                    @click="filter = segment.key">
                    <span class="size-2 rounded-full" :class="segment.dot"></span>
                    {{ segment.label }}
                    <span class="font-log text-xs opacity-70">{{ segment.count }}</span>
                </button>
            </div>

            <!-- Suche & Sortierung -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="ui-search flex-1">
                    <MagnifyingGlassIcon />
                    <input v-model="search" type="search" placeholder="Name, E-Mail, Telefon oder Bundesland …"
                        class="form-input w-full rounded-full!" aria-label="User suchen" />
                </div>
                <select v-model="sort" class="form-select rounded-full! sm:w-56" aria-label="Sortierung">
                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                </select>
            </div>

            <p class="ui-muted text-sm">{{ visible.length }} von {{ users.length }} Usern</p>

            <!-- Tabelle (ab Tablet) -->
            <div class="ui-card hidden overflow-x-auto md:block">
                <table class="ui-table min-w-full">
                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Abo</th>
                            <th scope="col">Bundesland</th>
                            <th scope="col" class="text-right">Fänge</th>
                            <th scope="col">Aktivität</th>
                            <th scope="col"><span class="sr-only">Aktionen</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in visible" :key="user.id">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                                        :class="user.is_admin ? 'bg-(--app-ink) text-(--app-surface)' : 'bg-(--app-accent-soft) text-(--app-accent-ink)'">
                                        {{ initials(user.name) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-1.5 font-semibold text-(--app-ink)">
                                            <span class="truncate">{{ user.name }}</span>
                                            <span v-if="user.is_admin" class="ui-badge">Admin</span>
                                            <span v-if="user.id === currentUserId" class="ui-badge">Du</span>
                                        </p>
                                        <p class="flex items-center gap-1.5 text-xs text-(--app-muted)">
                                            <a :href="`mailto:${user.email}`" class="truncate hover:text-(--app-accent-ink)">{{ user.email }}</a>
                                            <span v-if="!user.verified" class="ui-badge ui-badge-float" title="E-Mail-Adresse noch nicht bestätigt">unbestätigt</span>
                                        </p>
                                        <p v-if="user.tel" class="font-log text-xs text-(--app-subtle)">{{ user.tel }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="ui-badge" :class="PLANS[user.plan].badge">{{ PLANS[user.plan].label }}</span>
                                <p v-if="user.subscription_expires_at" class="mt-1 text-xs text-(--app-muted)">
                                    {{ user.plan === 'canceled' ? 'aktiv bis' : 'endete am' }} {{ formatDate(user.subscription_expires_at) }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap text-sm">
                                <span v-if="user.state">{{ user.state }}</span>
                                <span v-else class="text-(--app-subtle)">–</span>
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <p class="font-log text-(--app-ink)">{{ formatNumber(user.catches_count) }}</p>
                                <p v-if="user.last_catch_at" class="text-xs text-(--app-subtle)">zuletzt {{ relativeTime(user.last_catch_at) }}</p>
                                <p v-if="user.spots_count" class="text-xs text-(--app-subtle)">{{ user.spots_count }} Spots</p>
                            </td>
                            <td class="whitespace-nowrap text-sm">
                                <p :class="user.last_active_at ? 'text-(--app-ink)' : 'text-(--app-subtle)'">
                                    {{ user.last_active_at ? `aktiv ${relativeTime(user.last_active_at)}` : 'nicht eingeloggt' }}
                                </p>
                                <p class="text-xs text-(--app-subtle)">seit {{ formatDate(user.created_at) }}</p>
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <div class="flex justify-end gap-1">
                                    <a :href="`mailto:${user.email}`" class="ui-icon-btn" :aria-label="`${user.name} eine E-Mail schreiben`" title="E-Mail schreiben">
                                        <EnvelopeIcon class="size-4" />
                                    </a>
                                    <Link v-if="user.id !== currentUserId" :href="route('admin.impersonate', user.id)" class="ui-icon-btn"
                                        :aria-label="`Als ${user.name} ansehen`" title="App als dieser User ansehen">
                                        <EyeIcon class="size-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!visible.length">
                            <td colspan="6" class="py-10 text-center text-sm text-(--app-muted)">Keine User gefunden.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Karten (Handy) -->
            <ul class="space-y-3 md:hidden">
                <li v-for="user in visible" :key="user.id" class="ui-card p-4">
                    <div class="flex items-start gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                            :class="user.is_admin ? 'bg-(--app-ink) text-(--app-surface)' : 'bg-(--app-accent-soft) text-(--app-accent-ink)'">
                            {{ initials(user.name) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold">{{ user.name }}</p>
                            <a :href="`mailto:${user.email}`" class="block truncate text-xs text-(--app-muted)">{{ user.email }}</a>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="ui-badge" :class="PLANS[user.plan].badge">{{ PLANS[user.plan].label }}</span>
                                <span v-if="user.is_admin" class="ui-badge">Admin</span>
                                <span v-if="!user.verified" class="ui-badge ui-badge-float">unbestätigt</span>
                                <span v-if="user.state" class="ui-badge">{{ user.state }}</span>
                            </div>
                        </div>
                        <Link v-if="user.id !== currentUserId" :href="route('admin.impersonate', user.id)" class="ui-icon-btn shrink-0"
                            :aria-label="`Als ${user.name} ansehen`">
                            <EyeIcon class="size-4" />
                        </Link>
                    </div>
                    <dl class="mt-3 grid grid-cols-3 gap-2 border-t border-(--app-border) pt-3 text-xs">
                        <div><dt class="text-(--app-subtle)">Fänge</dt><dd class="font-log mt-0.5 text-sm">{{ formatNumber(user.catches_count) }}</dd></div>
                        <div><dt class="text-(--app-subtle)">Aktiv</dt><dd class="mt-0.5">{{ relativeTime(user.last_active_at) }}</dd></div>
                        <div><dt class="text-(--app-subtle)">Seit</dt><dd class="mt-0.5">{{ formatDate(user.created_at) }}</dd></div>
                    </dl>
                </li>
                <li v-if="!visible.length" class="ui-card px-4 py-10 text-center text-sm text-(--app-muted)">Keine User gefunden.</li>
            </ul>
        </div>
    </PageWrapper>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { EnvelopeIcon, EyeIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { formatDate, formatNumber, initials, normalize, relativeTime } from '@/utils/format'

const props = defineProps({
    users: { type: Array, default: () => [] },
})

const currentUserId = usePage().props.auth.user.id

const PLANS = {
    plus: { label: 'Plus', badge: 'ui-badge-success' },
    canceled: { label: 'Gekündigt', badge: 'ui-badge-float' },
    ended: { label: 'Abgelaufen', badge: 'ui-badge-danger' },
    free: { label: 'Gratis', badge: '' },
}

const FILTERS = {
    all: () => true,
    plus: (u) => u.plan === 'plus',
    canceled: (u) => u.plan === 'canceled' || u.plan === 'ended',
    free: (u) => u.plan === 'free',
    unverified: (u) => !u.verified,
    inactive: (u) => !u.catches_count,
    admins: (u) => u.is_admin,
}

const filter = ref('all')
const segments = computed(() => [
    { key: 'all', label: 'Alle', dot: 'bg-(--app-subtle)' },
    { key: 'plus', label: 'Plus', dot: 'bg-(--app-success)' },
    { key: 'canceled', label: 'Gekündigt', dot: 'bg-(--app-float)' },
    { key: 'free', label: 'Gratis', dot: 'bg-(--app-accent)' },
    { key: 'unverified', label: 'Unbestätigt', dot: 'bg-(--app-float)' },
    { key: 'inactive', label: 'Ohne Fänge', dot: 'bg-(--app-border-strong)' },
    { key: 'admins', label: 'Admins', dot: 'bg-(--app-ink)' },
].map(s => ({ ...s, count: props.users.filter(FILTERS[s.key]).length })))

const sortOptions = [
    { value: 'newest', label: 'Neueste zuerst' },
    { value: 'active', label: 'Zuletzt aktiv' },
    { value: 'catches', label: 'Meiste Fänge' },
    { value: 'name', label: 'Name A–Z' },
]
const sort = ref('newest')

const time = (date) => (date ? new Date(date).getTime() : 0)
const SORTERS = {
    newest: (a, b) => time(b.created_at) - time(a.created_at),
    active: (a, b) => time(b.last_active_at) - time(a.last_active_at),
    catches: (a, b) => b.catches_count - a.catches_count,
    name: (a, b) => a.name.localeCompare(b.name, 'de'),
}

const search = ref('')
const visible = computed(() => {
    const term = normalize(search.value.trim())
    return props.users
        .filter(FILTERS[filter.value])
        .filter(u => !term || [u.name, u.email, u.tel, u.state].some(field => normalize(field).includes(term)))
        .sort(SORTERS[sort.value])
})
</script>
