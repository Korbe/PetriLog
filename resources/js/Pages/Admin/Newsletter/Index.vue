<template>
    <PageWrapper title="Newsletter" eyebrow="Administration" backTo="/admin">
        <!-- Kennzahlen -->
        <div class="mb-5 grid grid-cols-2 gap-3 md:grid-cols-4">
            <div v-for="stat in statCards" :key="stat.label" class="ui-card p-4">
                <p class="text-xs font-medium text-(--app-muted)">{{ stat.label }}</p>
                <p class="font-log mt-1 text-2xl text-(--app-ink)">{{ formatNumber(stat.value) }}</p>
                <p class="mt-0.5 text-xs text-(--app-subtle)">{{ stat.hint }}</p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-12">
            <!-- Formular -->
            <form class="ui-card space-y-5 p-5 md:p-6 lg:col-span-6" @submit.prevent="submit">
                <VInput label="Betreff" v-model="form.subject" :error="form.errors.subject" />
                <VEditor label="Inhalt" v-model="form.content" :error="form.errors.content" />

                <!-- Empfänger -->
                <fieldset>
                    <legend class="mb-2 text-sm font-medium text-(--app-ink)">Empfänger</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label v-for="option in audiences" :key="option.value"
                            class="flex cursor-pointer gap-3 rounded-2xl border p-4 transition"
                            :class="form.audience === option.value
                                ? 'border-(--app-accent) bg-(--app-accent-soft)'
                                : 'border-(--app-border) hover:border-(--app-border-strong)'">
                            <input type="radio" class="form-radio mt-0.5" name="audience" :value="option.value" v-model="form.audience" />
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-(--app-ink)">{{ option.label }}</span>
                                <span class="mt-0.5 block text-xs text-(--app-muted)">{{ option.hint }}</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <!-- Rechtlicher Hinweis bei „an alle“ -->
                <div v-if="isService" class="rounded-2xl border border-(--app-float)/40 bg-[color-mix(in_srgb,var(--app-float)_8%,transparent)] p-4 text-sm">
                    <p class="flex items-center gap-2 font-semibold text-(--app-ink)">
                        <ExclamationTriangleIcon class="size-5 text-(--app-float)" /> Nur für echte Service-Mitteilungen
                    </p>
                    <p class="mt-2 text-(--app-muted)">
                        Wer den Newsletter abbestellt hat, darf keine Werbung mehr bekommen (TKG 2021, DSGVO).
                        Erlaubt sind nur Informationen, die das bestehende Konto betreffen – z. B. ein Redesign mit
                        geänderter Bedienung, neue AGB, Sicherheits- oder Wartungshinweise. Keine Angebote, Rabatte
                        oder Werbung für Plus.
                    </p>
                    <label class="mt-3 flex cursor-pointer items-start gap-2.5 text-(--app-ink)">
                        <input type="checkbox" class="form-checkbox mt-0.5" v-model="form.service_confirmed" />
                        <span>Ich bestätige: Diese E-Mail enthält ausschließlich Informationen zum Konto bzw. Dienst und keine Werbung.</span>
                    </label>
                    <p v-if="form.errors.service_confirmed" class="mt-2 text-xs font-medium text-(--app-danger)">{{ form.errors.service_confirmed }}</p>
                </div>

                <div class="flex flex-col gap-3 border-t border-(--app-border) pt-5 sm:flex-row">
                    <VButton type="submit" :disabled="!canSend">
                        <PaperAirplaneIcon class="size-4" />
                        An {{ formatNumber(recipientCount) }} User senden
                    </VButton>
                    <VButton type="button" variant="secondary" :disabled="form.processing" @click="sendTest">
                        Test an mich senden
                    </VButton>
                </div>
            </form>

            <!-- Vorschau -->
            <section class="lg:col-span-6" aria-label="Vorschau">
                <div class="lg:sticky lg:top-24">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <p class="ui-kicker">Vorschau</p>
                        <div class="flex items-center gap-2">
                            <span v-if="previewLoading" class="text-xs text-(--app-subtle)">aktualisiert …</span>
                            <div class="flex rounded-full border border-(--app-border) p-0.5" role="group" aria-label="Breite">
                                <button v-for="d in devices" :key="d.key" type="button"
                                    class="rounded-full px-3 py-1 text-xs font-semibold transition"
                                    :class="device === d.key ? 'bg-(--app-ink) text-(--app-surface)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                                    :aria-pressed="device === d.key" @click="device = d.key">{{ d.label }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="ui-card overflow-hidden">
                        <div class="border-b border-(--app-border) px-4 py-3 text-xs">
                            <p class="text-(--app-subtle)">Von: PetriLog · An: {{ $page.props.auth.user.email }}</p>
                            <p class="mt-0.5 truncate font-semibold text-(--app-ink)">{{ form.subject || 'Betreff' }}</p>
                        </div>
                        <div class="flex justify-center bg-(--app-surface-2)">
                            <iframe :srcdoc="previewHtml" title="Vorschau der E-Mail" sandbox=""
                                class="h-[70vh] min-h-[480px] border-0 bg-white transition-all"
                                :style="{ width: device === 'mobile' ? '375px' : '100%' }" />
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-(--app-subtle)">So sieht die E-Mail für Empfänger aus (Beispiel: dein Konto).</p>
                </div>
            </section>
        </div>
    </PageWrapper>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VEditor from '@/components/VEditor.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import { confirmDialog } from '@/composables/useDialog'
import { formatNumber } from '@/utils/format'
import { ExclamationTriangleIcon, PaperAirplaneIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    stats: { type: Object, required: true },
})

const form = useForm({
    subject: '',
    content: '',
    audience: 'subscribers',
    service_confirmed: false,
})

const isService = computed(() => form.audience === 'all')
const recipientCount = computed(() => (isService.value ? props.stats.all : props.stats.subscribers))

const statCards = computed(() => [
    { label: 'Empfänger', value: props.stats.subscribers, hint: 'Newsletter angemeldet' },
    { label: 'Abgemeldet', value: props.stats.optedOut, hint: 'bekommen keinen Newsletter' },
    { label: 'Alle bestätigten', value: props.stats.all, hint: 'für Service-Mitteilungen' },
    { label: 'Unbestätigt', value: props.stats.unverified, hint: 'bekommen nie E-Mails' },
])

const audiences = computed(() => [
    { value: 'subscribers', label: 'Newsletter', hint: `${formatNumber(props.stats.subscribers)} angemeldete User` },
    { value: 'all', label: 'Service-Mitteilung an alle', hint: `${formatNumber(props.stats.all)} User, inkl. ${formatNumber(props.stats.optedOut)} Abgemeldete` },
])

const canSend = computed(() => !form.processing
    && form.subject.trim() && form.content.trim()
    && (!isService.value || form.service_confirmed)
    && recipientCount.value > 0)

watch(() => form.audience, () => (form.service_confirmed = false))

async function submit() {
    const ok = await confirmDialog({
        title: isService.value ? 'Service-Mitteilung an alle senden?' : 'Newsletter senden?',
        message: `„${form.subject}“ geht an ${formatNumber(recipientCount.value)} User`
            + (isService.value ? `, auch an ${formatNumber(props.stats.optedOut)} Abgemeldete.` : '.')
            + ' Das lässt sich nicht zurückholen.',
        confirmText: 'Jetzt senden',
        tone: isService.value ? 'warning' : 'primary',
    })
    if (ok) {
        form.post(route('admin.newsletter.send'), { preserveScroll: true })
    }
}

function sendTest() {
    form.post(route('admin.newsletter.test'), { preserveScroll: true })
}

/* Vorschau – serverseitig gerendert, also exakt die echte E-Mail */
const devices = [
    { key: 'desktop', label: 'Desktop' },
    { key: 'mobile', label: 'Handy' },
]
const device = ref('desktop')
const previewHtml = ref('')
const previewLoading = ref(false)
let timer = null
let controller = null

async function loadPreview() {
    controller?.abort()
    controller = new AbortController()
    previewLoading.value = true
    try {
        const { data } = await axios.post(route('admin.newsletter.preview'), {
            subject: form.subject,
            content: form.content,
            audience: form.audience,
        }, { signal: controller.signal, responseType: 'text' })
        previewHtml.value = data
    } catch (error) {
        if (!axios.isCancel(error)) {
            previewHtml.value = '<p style="font-family:sans-serif;padding:24px;color:#c62828">Vorschau konnte nicht geladen werden.</p>'
        }
    } finally {
        previewLoading.value = false
    }
}

watch(() => [form.subject, form.content, form.audience], () => {
    clearTimeout(timer)
    timer = setTimeout(loadPreview, 400)
}, { immediate: true })

onBeforeUnmount(() => {
    clearTimeout(timer)
    controller?.abort()
})
</script>
