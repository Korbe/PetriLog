<script setup>
// Shared detail view for lakes and rivers in the Gewässer-Wiki.
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VButton from '@/components/VButton.vue';
import { PencilSquareIcon, LightBulbIcon, ScaleIcon, TicketIcon, InformationCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    water: { type: Object, required: true },
    // 'lake' | 'river'
    kind: { type: String, required: true },
    stateId: { type: [String, Number], required: true },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.is_admin === true);

const label = computed(() => (props.kind === 'lake' ? 'See' : 'Fluss'));

const facts = computed(() => [
    { key: 'fishing_rights', title: 'Fischereirechte', icon: ScaleIcon, html: props.water.fishing_rights },
    { key: 'ticket_sales', title: 'Kartenverkauf', icon: TicketIcon, html: props.water.ticket_sales },
].filter(f => f.html));

const fish = computed(() => [...(props.water.fish ?? [])].sort((a, b) => a.name.localeCompare(b.name, 'de')));
</script>

<template>
    <PageWrapper :title="water.name" :eyebrow="`Gewässer-Wiki · ${label}`" :backTo="`/states/${stateId}`">
        <template v-if="isAdmin" v-slot:actions>
            <VButton variant="secondary" :href="`/admin/${kind}/${water.id}/edit`">
                <PencilSquareIcon class="size-4" /> {{ label }} bearbeiten
            </VButton>
        </template>

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">
            <div class="space-y-5 md:space-y-6 lg:col-span-7">
                <section class="ui-card p-5 md:p-7">
                    <p class="ui-kicker">Über den {{ label }}</p>
                    <div v-if="water.desc" class="ui-prose mt-3" v-html="water.desc"></div>
                    <p v-else class="ui-muted mt-3 text-sm">Für dieses Gewässer gibt es noch keine Beschreibung.</p>
                </section>

                <section v-for="fact in facts" :key="fact.key" class="ui-card p-5 md:p-7">
                    <div class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-(--app-surface-2) text-(--app-muted)">
                            <component :is="fact.icon" class="size-5" />
                        </span>
                        <h2 class="text-base font-semibold">{{ fact.title }}</h2>
                    </div>
                    <div class="ui-prose mt-3" v-html="fact.html"></div>
                </section>
            </div>

            <aside class="space-y-5 md:space-y-6 lg:col-span-5">
                <section v-if="water.hint"
                    class="relative overflow-hidden rounded-[1.25rem] bg-linear-to-br from-[#06212b] via-[#0a3140] to-[#104456] p-5 text-[#f3eee3] shadow-(--app-shadow) md:p-6">
                    <div class="absolute -right-10 -top-12 size-40 rounded-full bg-primary-500/25 blur-3xl" aria-hidden="true"></div>
                    <div class="relative">
                        <p class="font-log flex items-center gap-2 text-[11px] uppercase tracking-[0.2em] text-[#ff8a5c]">
                            <LightBulbIcon class="size-4" /> Tipp vom Wasser
                        </p>
                        <div class="ui-prose mt-3 text-white/85! [&_strong]:text-white! [&_a]:text-[#9fd0f9]!" v-html="water.hint"></div>
                    </div>
                </section>

                <section class="ui-card p-5 md:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <p class="ui-kicker">Heimische Fische</p>
                        <span class="ui-badge">{{ fish.length }}</span>
                    </div>
                    <div v-if="fish.length" class="mt-4 flex flex-wrap gap-2">
                        <Link v-for="f in fish" :key="f.id" :href="`/fish/${f.id}`"
                            class="rounded-full border border-(--app-border-strong) px-3.5 py-1.5 text-sm font-medium transition hover:border-primary-500 hover:text-(--app-accent-ink)">
                            {{ f.name }}
                        </Link>
                    </div>
                    <p v-else class="ui-muted mt-3 text-sm">Noch keine Fischarten hinterlegt.</p>
                </section>

                <div class="ui-panel flex items-start gap-3 px-4 py-3 text-sm text-(--app-muted)">
                    <InformationCircleIcon class="mt-0.5 size-5 shrink-0 text-(--app-accent-ink)" />
                    <span>Infos veraltet oder unvollständig? Schreib uns an
                        <a href="mailto:info@petrilog.com" class="ui-link">info@petrilog.com</a>.</span>
                </div>
            </aside>
        </div>
    </PageWrapper>
</template>
