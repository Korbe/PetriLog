<template>
    <PageWrapper :title="association.name" eyebrow="Gewässer-Wiki · Verein" :backTo="`/states/${state_id}`">
        <template v-if="isAdmin" v-slot:actions>
            <VButton variant="secondary" :href="`/admin/association/${association.id}/edit`">
                <PencilSquareIcon class="size-4" /> Verein bearbeiten
            </VButton>
        </template>

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">
            <section class="ui-card p-5 md:p-7 lg:col-span-8">
                <p class="ui-kicker">Über den Verein</p>
                <div v-if="association.desc" class="ui-prose mt-3" v-html="association.desc"></div>
                <p v-else class="ui-muted mt-3 text-sm">Für diesen Verein gibt es noch keine Beschreibung.</p>
            </section>

            <aside class="space-y-5 lg:col-span-4">
                <a v-if="association.link" :href="association.link" target="_blank" rel="noopener"
                    class="ui-card ui-card-hover group flex items-center gap-4 p-5">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                        <GlobeAltIcon class="size-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs text-(--app-subtle)">Website des Vereins</span>
                        <span class="block truncate font-semibold">{{ shortenLink(association.link) }}</span>
                    </span>
                    <ArrowTopRightOnSquareIcon class="size-5 shrink-0 text-(--app-subtle) transition group-hover:text-(--app-accent-ink)" />
                </a>

                <div class="ui-panel flex items-start gap-3 px-4 py-3 text-sm text-(--app-muted)">
                    <InformationCircleIcon class="mt-0.5 size-5 shrink-0 text-(--app-accent-ink)" />
                    <span>Infos zum Verein veraltet? Schreib uns an
                        <a href="mailto:info@petrilog.com" class="ui-link">info@petrilog.com</a>.</span>
                </div>
            </aside>
        </div>

    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import {
    ArrowTopRightOnSquareIcon, GlobeAltIcon, PencilSquareIcon, InformationCircleIcon,
} from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

defineProps({
    state_id: [String, Number],
    association: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.is_admin === true;
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});

const shortenLink = (link) => {
    if (!link) return '';
    let short = link.replace(/^https?:\/\//, ''); // Entferne http:// oder https://
    short = short.replace(/\/$/, ''); // Entferne / am Ende
    return short;
};

</script>
