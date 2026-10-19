<template>
    <PageWrapper :title="fish.name" eyebrow="Fisch-Wiki">
        <template v-slot:actions>
            <VButton variant="ghost" @click="reporting = true" title="Falsche oder fehlende Angaben melden">
                <FlagIcon class="size-4" /> Fehler melden
            </VButton>
            <VButton v-if="isAdmin" variant="secondary" :href="`/admin/fish/${fish.id}/edit`">
                <PencilSquareIcon class="size-4" /> Fisch bearbeiten
            </VButton>
        </template>

        <ReportCorrectionDialog :show="reporting" type="fish" :id="fish.id" :name="fish.name" @close="reporting = false" />

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">
            <!-- illustrations have a white background: keep the frame white in both themes.
                 Desktop: nicht auf die Texthöhe strecken, sondern oben andocken und beim Scrollen mitfahren -->
            <div class="ui-card flex items-center justify-center overflow-hidden bg-white! p-6 md:p-10 lg:sticky lg:top-24 lg:col-span-7 lg:self-start">
                <img :src="fish.media[0]?.url || '/images/fish-default.png'" :alt="fish.name"
                    class="max-h-80 w-full object-contain" />
            </div>

            <section class="ui-card p-5 md:p-7 lg:col-span-5">
                <p class="ui-kicker">Steckbrief</p>
                <h2 class="font-display mt-1 text-4xl">{{ fish.name }}</h2>
                <div v-if="fish.desc" class="ui-prose mt-4" v-html="fish.desc"></div>
                <p v-else class="ui-muted mt-4 text-sm">Für diese Fischart gibt es noch keine Beschreibung.</p>
            </section>
        </div>

    </PageWrapper>
</template>

<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { FlagIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import ReportCorrectionDialog from '@/components/ReportCorrectionDialog.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const reporting = ref(false);

defineProps({
    fish: Object
});

const page = usePage();

const isAdmin = computed(() => {
    return page.props.auth?.user?.is_staff === true;
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});
</script>
