<template>
    <PageWrapper :title="state.name" eyebrow="Gewässer-Wiki · Bundesland" :backTo="`/states`">
        <template v-if="isAdmin" v-slot:actions>
            <VButton variant="secondary" :href="`/admin/state/${state.id}/edit`">
                <PencilSquareIcon class="size-4" /> Bundesland bearbeiten
            </VButton>
        </template>

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">

            <!-- Directory -->
            <section class="ui-card flex flex-col overflow-hidden lg:col-span-7" aria-label="Gewässer und Vereine">
                <div class="border-b border-(--app-border) p-4 md:p-5">
                    <div class="flex gap-1 rounded-full bg-(--app-surface-2) p-1" role="tablist">
                        <button v-for="tab in tabs" :key="tab.key" type="button" role="tab" :aria-selected="activeTab === tab.key"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-full px-3 py-2 text-sm font-semibold transition"
                            :class="activeTab === tab.key ? 'bg-(--app-surface) text-(--app-ink) shadow-(--app-shadow)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                            @click="activeTab = tab.key">
                            {{ tab.label }}
                            <span class="font-log text-[11px] text-(--app-subtle)">{{ tab.items.length }}</span>
                        </button>
                    </div>

                    <div class="ui-search mt-3">
                        <MagnifyingGlassIcon />
                        <input v-model="search" type="search" class="form-input" :placeholder="`${currentTab.label} durchsuchen …`"
                            :aria-label="`${currentTab.label} durchsuchen`" />
                    </div>
                </div>

                <ul class="max-h-[34rem] flex-1 overflow-y-auto p-2" role="tabpanel">
                    <li v-for="item in filteredItems" :key="item.id">
                        <Link :href="`${currentTab.baseUrl}/${item.id}`" class="ui-row group items-start">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl" :class="currentTab.tone">
                                <component :is="currentTab.icon" class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-semibold">{{ item.name }}</span>
                                <span v-if="activeTab === 'associations' && item.desc" class="mt-0.5 block text-sm text-(--app-muted)">
                                    {{ truncateDesc(item.desc) }}
                                </span>
                            </span>
                            <ChevronRightIcon class="mt-2 size-4 shrink-0 text-(--app-subtle) transition group-hover:translate-x-0.5" />
                        </Link>
                    </li>
                    <li v-if="!filteredItems.length" class="px-4 py-10 text-center text-sm text-(--app-muted)">
                        {{ search ? `Keine Treffer für „${search}“.` : `Noch keine ${currentTab.label} eingetragen.` }}
                    </li>
                </ul>
            </section>

            <!-- About -->
            <section class="space-y-5 lg:col-span-5">
                <div class="ui-card flex items-center gap-5 p-5 md:p-6">
                    <img v-if="stateImage" :src="stateImage" :alt="`Wappen ${state.name}`"
                        class="h-20 w-16 shrink-0 object-contain" />
                    <dl class="grid flex-1 grid-cols-3 gap-2 text-center">
                        <div v-for="tab in tabs" :key="tab.key" class="ui-panel px-2 py-3">
                            <dt class="text-[11px] text-(--app-subtle)">{{ tab.label }}</dt>
                            <dd class="font-display text-3xl leading-none">{{ tab.items.length }}</dd>
                        </div>
                    </dl>
                </div>

                <div v-if="state.desc" class="ui-card p-5 md:p-6">
                    <p class="ui-kicker">Über {{ state.name }}</p>
                    <div class="ui-prose mt-3" v-html="state.desc"></div>
                </div>

                <div class="ui-panel flex items-start gap-3 px-4 py-3 text-sm text-(--app-muted)">
                    <InformationCircleIcon class="mt-0.5 size-5 shrink-0 text-(--app-accent-ink)" />
                    <span>Ein Gewässer oder Verein fehlt? Schreib uns an
                        <a href="mailto:info@petrilog.com" class="ui-link">info@petrilog.com</a>.</span>
                </div>
            </section>
        </div>

    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import {
    ChevronRightIcon, MagnifyingGlassIcon, PencilSquareIcon, InformationCircleIcon, UserGroupIcon,
} from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';
import { computed, h, onMounted, ref, watch } from 'vue';

const props = defineProps({
    state: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.is_staff === true;
});

const LakeIcon = (p) => h('svg', { ...p, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.6 }, [
    h('path', { d: 'M4 13c0-4 3.6-7 8-7s8 3 8 7-3.6 6-8 6-8-2-8-6Z' }),
    h('path', { d: 'M8 13c1-.8 2-.8 3 0s2 .8 3 0 2-.8 3 0', 'stroke-linecap': 'round' }),
]);
const RiverIcon = (p) => h('svg', { ...p, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', 'stroke-width': 1.6, 'stroke-linecap': 'round' }, [
    h('path', { d: 'M3 8c3-2 6 2 9 0s6-2 9 0' }),
    h('path', { d: 'M3 13c3-2 6 2 9 0s6-2 9 0' }),
    h('path', { d: 'M3 18c3-2 6 2 9 0s6-2 9 0' }),
]);

const tabs = computed(() => [
    { key: 'lakes', label: 'Seen', items: props.state.lakes ?? [], baseUrl: `/states/${props.state.id}/lakes`, icon: LakeIcon, tone: 'bg-(--app-accent-soft) text-(--app-accent-ink)' },
    { key: 'rivers', label: 'Flüsse', items: props.state.rivers ?? [], baseUrl: `/states/${props.state.id}/rivers`, icon: RiverIcon, tone: 'bg-(--app-success-soft) text-(--app-success)' },
    { key: 'associations', label: 'Vereine', items: props.state.associations ?? [], baseUrl: `/states/${props.state.id}/associations`, icon: UserGroupIcon, tone: 'bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-(--app-float)' },
]);

const stateImage = computed(() => props.state.media?.[0]?.original_url ?? props.state.media?.[0]?.url ?? null);

const activeTab = ref(tabs.value.find(t => t.items.length)?.key ?? 'lakes');
const search = ref('');
const currentTab = computed(() => tabs.value.find(t => t.key === activeTab.value));

const filteredItems = computed(() => {
    const term = search.value.trim().toLowerCase();
    const items = [...currentTab.value.items].sort((a, b) => a.name.localeCompare(b.name, 'de'));
    return term ? items.filter(i => i.name.toLowerCase().includes(term)) : items;
});

watch(activeTab, () => { search.value = ''; });

const truncateDesc = (desc) => {
    if (!desc) return '';
    const plainText = desc.replace(/<[^>]*>/g, ''); // Entferne HTML-Tags für sichere Kürzung
    return plainText.length > 100 ? plainText.substring(0, 100) + '…' : plainText;
};

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
