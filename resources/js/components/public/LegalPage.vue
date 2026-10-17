<script setup>
// Shared frame for the legal pages: dark hero, sticky table of contents, prose column.
import Layout from '@/Layouts/Public/Layout.vue';
import PageHero from './PageHero.vue';

defineProps({
    // [{ id, title }] — rendered as table of contents
    sections: { type: Array, required: true },
    updated: { type: String, default: null },
});
</script>

<template>
    <Layout overlay>
        <PageHero eyebrow="Rechtliches">
            <template #title>
                <slot name="title" />
            </template>
            <slot name="intro" />
        </PageHero>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
            <div class="grid gap-12 lg:grid-cols-12">
                <aside class="hidden lg:col-span-3 lg:block">
                    <nav class="sticky top-28" aria-label="Inhalt">
                        <p class="font-log text-[11px] uppercase tracking-[0.2em] text-(--ink)/45">Inhalt</p>
                        <ol class="mt-4 space-y-2 border-l border-(--ink)/10">
                            <li v-for="(s, i) in sections" :key="s.id">
                                <a :href="`#${s.id}`"
                                    class="-ml-px flex gap-3 border-l border-transparent py-1 pl-4 text-sm text-(--ink)/60 transition hover:border-(--float) hover:text-(--ink)">
                                    <span class="font-log text-[11px] text-(--ink)/35">{{ String(i + 1).padStart(2, '0') }}</span>
                                    {{ s.title }}
                                </a>
                            </li>
                        </ol>
                        <p v-if="updated" class="font-log mt-8 text-[11px] uppercase tracking-[0.15em] text-(--ink)/40">
                            Stand: {{ updated }}
                        </p>
                    </nav>
                </aside>

                <div class="lg:col-span-9">
                    <slot />
                    <p v-if="updated" class="font-log mt-12 text-xs uppercase tracking-[0.15em] text-(--ink)/45 lg:hidden">
                        Stand: {{ updated }}
                    </p>
                </div>
            </div>
        </div>
    </Layout>
</template>
