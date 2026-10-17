<script setup>
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import NewsletterForm from './Partials/NewsletterForm.vue';
import StateForm from './Partials/StateForm.vue';

import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    states: Array,
});

const page = usePage();
const initials = computed(() => (page.props.auth.user?.name ?? '?')
    .split(/\s+/).filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join(''));

const jet = computed(() => page.props.jetstream ?? {});
const sections = computed(() => [
    { id: 'profil', label: 'Profil-Informationen', show: jet.value.canUpdateProfileInformation },
    { id: 'passwort', label: 'Passwort', show: jet.value.canUpdatePassword },
    { id: 'bundesland', label: 'Bundesland', show: true },
    { id: 'newsletter', label: 'Newsletter', show: true },
    { id: 'sicherheit', label: 'Zwei-Faktor-Authentifizierung', show: jet.value.canManageTwoFactorAuthentication },
    { id: 'sitzungen', label: 'Browsersitzungen', show: true },
    { id: 'konto-loeschen', label: 'Konto löschen', show: jet.value.hasAccountDeletionFeatures },
].filter(s => s.show));
</script>

<template>
    <PageWrapper title="Profil" eyebrow="Konto & Einstellungen" backTo="/dashboard">
        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">

            <!-- Overview -->
            <aside class="lg:col-span-4">
                <div class="ui-card p-6 lg:sticky lg:top-24">
                    <div class="flex items-center gap-4">
                        <span class="flex size-14 shrink-0 items-center justify-center rounded-full bg-linear-to-br from-primary-400 to-primary-700 text-lg font-semibold text-white">
                            {{ initials }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-lg font-semibold">{{ $page.props.auth.user.name }}</p>
                            <p class="truncate text-sm text-(--app-muted)">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="ui-badge" :class="$page.props.auth.user.subscribed ? 'ui-badge-float' : ''">
                            {{ $page.props.auth.user.subscribed ? 'PetriLog Plus' : 'Gratis-Plan' }}
                        </span>
                        <span class="ui-badge" :class="$page.props.auth.user.verified ? 'ui-badge-success' : 'ui-badge-danger'">
                            {{ $page.props.auth.user.verified ? 'E-Mail bestätigt' : 'E-Mail nicht bestätigt' }}
                        </span>
                        <span v-if="$page.props.auth.user.state" class="ui-badge">{{ $page.props.auth.user.state.name }}</span>
                    </div>
                    <a href="/billing-portal"
                        class="mt-6 flex items-center justify-between rounded-2xl bg-(--app-surface-2) px-4 py-3 text-sm font-semibold transition hover:bg-(--app-hover)">
                        Abo verwalten <span aria-hidden="true">→</span>
                    </a>
                    <nav class="mt-5 hidden space-y-1 border-t border-(--app-border) pt-5 text-sm lg:block" aria-label="Profil-Bereiche">
                        <a v-for="link in sections" :key="link.id" :href="`#${link.id}`"
                            class="block rounded-xl px-3 py-2 text-(--app-muted) transition hover:bg-(--app-hover) hover:text-(--app-ink)">{{ link.label }}</a>
                    </nav>
                </div>
            </aside>

            <div class="space-y-5 md:space-y-6 lg:col-span-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation" id="profil" class="scroll-mt-24">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword" id="passwort" class="scroll-mt-24">
                    <UpdatePasswordForm />
                </div>

                <div id="bundesland" class="scroll-mt-24">
                    <StateForm :user="$page.props.auth.user" :states="states" />
                </div>

                <div id="newsletter" class="scroll-mt-24">
                    <NewsletterForm :user="$page.props.auth.user" />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication" id="sicherheit" class="scroll-mt-24">
                    <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication" />
                </div>

                <div id="sitzungen" class="scroll-mt-24">
                    <LogoutOtherBrowserSessionsForm :sessions="sessions" />
                </div>

                <div v-if="$page.props.jetstream.hasAccountDeletionFeatures" id="konto-loeschen" class="scroll-mt-24">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </PageWrapper>
</template>
