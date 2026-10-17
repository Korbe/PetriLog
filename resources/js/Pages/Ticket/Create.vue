<template>
    <PageWrapper title="Ticket erstellen" eyebrow="Hilfe & Feedback" backTo="/dashboard">
        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">

            <form @submit.prevent="submitReport" class="ui-card space-y-5 p-5 md:p-7 lg:col-span-8">
                <VInput label="Titel" mandatory v-model="form.title" :error="errors.title"
                    placeholder="z. B. Foto lässt sich nicht hochladen" />

                <div>
                    <p class="mb-2 text-sm font-medium text-(--app-ink)">Kategorie</p>
                    <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Kategorie">
                        <button v-for="cat in categories" :key="cat.value" type="button" role="radio"
                            :aria-checked="form.category === cat.value" @click="form.category = cat.value"
                            class="rounded-full border px-3.5 py-1.5 text-sm font-medium transition"
                            :class="form.category === cat.value
                                ? 'border-primary-500 bg-(--app-accent-soft) text-(--app-accent-ink)'
                                : 'border-(--app-border-strong) text-(--app-muted) hover:border-(--app-subtle) hover:text-(--app-ink)'">
                            {{ cat.label }}
                        </button>
                    </div>
                    <div v-if="errors.category" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors.category }}</div>
                </div>

                <VEditor label="Beschreibung" v-model="form.description" :error="errors.description" />

                <VEditor label="Schritte zur Reproduktion (optional)" v-model="form.steps" :error="errors.steps" />

                <VInput label="Betroffene Seite / URL (optional)" v-model="form.url" placeholder="/dashboard"
                    :error="errors.url" />

                <div class="flex justify-end border-t border-(--app-border) pt-5">
                    <VButton type="submit" size="lg" :disabled="form.processing" :loading="form.processing">
                        <PaperAirplaneIcon v-if="!form.processing" class="size-5" /> Ticket absenden
                    </VButton>
                </div>
            </form>

            <aside class="space-y-5 lg:col-span-4">
                <div class="ui-card p-5 md:p-6">
                    <p class="ui-kicker">So hilfst du uns am besten</p>
                    <ol class="mt-4 space-y-3 text-sm">
                        <li v-for="(tip, i) in tips" :key="tip" class="flex gap-3">
                            <span class="font-log flex size-6 shrink-0 items-center justify-center rounded-full bg-(--app-accent-soft) text-[11px] text-(--app-accent-ink)">{{ i + 1 }}</span>
                            <span class="text-(--app-muted)">{{ tip }}</span>
                        </li>
                    </ol>
                </div>
                <div class="ui-panel px-4 py-3 text-sm text-(--app-muted)">
                    Lieber per Mail? Schreib an <a href="mailto:info@petrilog.com" class="ui-link">info@petrilog.com</a>.
                </div>
            </aside>
        </div>
    </PageWrapper>
</template>

<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import VInput from '@/components/VInput.vue';
import VButton from '@/components/VButton.vue';
import { useForm } from '@inertiajs/vue3';
import VEditor from '@/components/VEditor.vue';
import { PaperAirplaneIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    errors: Object,
})

const categories = [
    { label: 'UI / Design', value: 'ui' },
    { label: 'Funktion', value: 'functionality' },
    { label: 'Performance', value: 'performance' },
    { label: 'Fehlermeldung', value: 'error' },
    { label: 'Login / Zugriff', value: 'auth' },
    { label: 'Abo / Abrechnung', value: 'abo' },
    { label: 'Datenproblem', value: 'data_issue' },
    { label: 'Feature-Wunsch', value: 'feature_request' },
    { label: 'Sonstiges', value: 'other' },
];

const tips = [
    'Beschreibe kurz, was du erwartet hast und was stattdessen passiert ist.',
    'Bei Fehlern: Welche Schritte führen dorthin? Auf welchem Gerät & Browser?',
    'Feature-Wünsche sind ausdrücklich willkommen – je konkreter, desto besser.',
];

const form = useForm({
    title: '',
    description: '',
    category: '',
    steps: '',
    url: ''
});

const submitReport = () => {
    form.post(route('app.ticket.store'));
}
</script>
