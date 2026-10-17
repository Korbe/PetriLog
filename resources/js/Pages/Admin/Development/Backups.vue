<template>
    <PageWrapper title="Datenbank-Backups" eyebrow="Development · Daten & Sicherung" backTo="/admin/development">
        <template v-slot:actions>
            <VButton :loading="creating" :disabled="busy || creating" @click="createBackup">
                <CircleStackIcon class="size-4" /> Backup jetzt erstellen
            </VButton>
        </template>

        <div class="space-y-6">
            <!-- Konfiguration -->
            <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="card in configCards" :key="card.label" class="ui-card p-4">
                    <dt class="flex items-center gap-2 text-xs font-medium text-(--app-muted)">
                        <component :is="card.icon" class="size-4" /> {{ card.label }}
                    </dt>
                    <dd class="mt-2 flex items-center gap-2 font-semibold" :class="card.ok ? 'text-(--app-ink)' : 'text-(--app-float)'">
                        <CheckCircleIcon v-if="card.ok" class="size-5 shrink-0 text-(--app-success)" />
                        <ExclamationTriangleIcon v-else class="size-5 shrink-0" />
                        <span class="truncate">{{ card.value }}</span>
                    </dd>
                    <dd class="mt-1 truncate text-xs text-(--app-subtle)" :title="card.detail">{{ card.detail }}</dd>
                </div>
            </dl>

            <div v-if="busy" class="flex items-center gap-3 rounded-2xl bg-(--app-accent-soft) px-4 py-3 text-sm text-(--app-accent-ink)" role="status">
                <span class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                Ein Backup-Vorgang läuft im Hintergrund – die Seite aktualisiert sich automatisch.
            </div>

            <!-- Lokale Backups -->
            <section class="ui-card overflow-hidden">
                <header class="flex flex-wrap items-center justify-between gap-2 border-b border-(--app-border) px-5 py-4">
                    <div>
                        <p class="ui-kicker">Backups auf diesem Server</p>
                        <p class="ui-muted mt-0.5 text-xs">Verschlüsselte ZIP-Dateien außerhalb des Webverzeichnisses.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="ui-badge">{{ backups.length }}</span>
                        <input ref="fileInput" type="file" accept=".zip,application/zip" class="sr-only" @change="startUpload" />
                        <VButton size="sm" variant="secondary" :disabled="upload.active" @click="fileInput.click()">
                            <ArrowUpTrayIcon class="size-4" /> Backup hochladen
                        </VButton>
                    </div>
                </header>

                <!-- Upload-Fortschritt -->
                <div v-if="upload.active || upload.error || upload.done" class="border-b border-(--app-border) px-5 py-3 text-sm" role="status">
                    <template v-if="upload.active">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-log truncate text-xs">{{ upload.name }}</span>
                            <span class="font-log shrink-0 text-xs text-(--app-muted)">{{ upload.percent }} %</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-(--app-surface-2)">
                            <div class="h-full rounded-full bg-(--app-accent) transition-[width]" :style="{ width: `${upload.percent}%` }"></div>
                        </div>
                    </template>
                    <p v-else-if="upload.error" class="font-medium text-(--app-danger)">{{ upload.error }}</p>
                    <p v-else class="font-medium text-(--app-success)">
                        {{ upload.name }} hochgeladen – wird jetzt vollständig geprüft. Ungültige oder anders verschlüsselte Dateien werden automatisch entfernt.
                    </p>
                </div>

                <ul v-if="backups.length" class="divide-y divide-(--app-border)">
                    <li v-for="backup in backups" :key="backup.name" class="flex flex-col gap-3 px-5 py-4 lg:flex-row lg:items-center">
                        <div class="min-w-0 flex-1">
                            <p class="font-log truncate text-sm text-(--app-ink)">{{ backup.name }}</p>
                            <p class="mt-0.5 text-xs text-(--app-muted)">
                                {{ formatDateTime(backup.created_at) }} · {{ formatSize(backup.size) }}
                                <span v-if="backup.sha256" class="font-log" :title="backup.sha256"> · SHA-256 {{ backup.sha256.slice(0, 12) }}…</span>
                            </p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="ui-badge" :class="backup.transferred ? 'ui-badge-success' : (config.remote ? 'ui-badge-float' : '')">
                                    {{ backup.transferred ? 'Auf Zweitserver' : 'Nur lokal' }}
                                </span>
                                <span v-if="!backup.sha256" class="ui-badge ui-badge-float">Wird geprüft</span>
                                <span v-if="backup.verified_at" class="ui-badge ui-badge-success">Geprüft {{ relativeTime(backup.verified_at) }}</span>
                                <span v-if="backup.restore_tested_at" class="ui-badge ui-badge-success">Wiederherstellung getestet</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <VButton size="sm" variant="secondary" :disabled="busy" @click="verify(backup)">
                                <ShieldCheckIcon class="size-4" /> Prüfen
                            </VButton>
                            <VButton size="sm" variant="secondary" :disabled="busy" @click="restoreTest(backup)">
                                <ArrowPathIcon class="size-4" /> Wiederherstellung testen
                            </VButton>
                            <a :href="route('admin.development.backups.download', backup.name)"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold text-(--app-muted) transition hover:bg-(--app-hover) hover:text-(--app-ink)"
                                title="Verschlüsselte ZIP-Datei herunterladen">
                                <ArrowDownTrayIcon class="size-4" /> Download
                            </a>
                            <VButton size="sm" variant="ghost" class="text-(--app-danger)!" :disabled="busy || !backup.sha256"
                                :title="backup.sha256 ? 'Diesen Stand produktiv wiederherstellen' : 'Erst nach erfolgreicher Prüfung möglich'"
                                @click="openRestore(backup)">
                                <ArrowUturnLeftIcon class="size-4" /> Wiederherstellen
                            </VButton>
                            <button type="button" class="ui-icon-btn" :disabled="busy" :aria-label="`${backup.name} löschen`"
                                title="Von diesem Server löschen" @click="removeBackup(backup)">
                                <TrashIcon class="size-4" />
                            </button>
                        </div>
                    </li>
                </ul>
                <div v-else class="px-5 py-10 text-center text-sm text-(--app-muted)">
                    <template v-if="config.remote && config.keep_local === 0">
                        Auf diesem Server wird bewusst kein Backup aufbewahrt – sie liegen auf
                        <strong class="text-(--app-ink)">{{ config.remote.host }}</strong> (täglich, Sonntag, monatlich).<br>
                        Zum Wiederherstellen die gewünschte Datei von dort herunterladen und hier hochladen.
                    </template>
                    <template v-else>
                        Noch keine Backups vorhanden. Erstelle das erste über den Button oben oder per <code class="font-log">php artisan backup:run</code>.
                    </template>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-12">
                <!-- Verlauf -->
                <section class="ui-card overflow-hidden xl:col-span-8">
                    <header class="border-b border-(--app-border) px-5 py-4">
                        <p class="ui-kicker">Verlauf</p>
                    </header>
                    <ul v-if="runs.length" class="divide-y divide-(--app-border)">
                        <li v-for="run in runs" :key="run.id" class="flex items-start gap-3 px-5 py-3">
                            <span class="mt-1 size-2.5 shrink-0 rounded-full" :class="STATUS[run.status]?.dot ?? 'bg-(--app-subtle)'"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm">
                                    <span class="font-semibold">{{ ACTIONS[run.action] ?? run.action }}</span>
                                    <span class="ui-badge ml-1.5" :class="STATUS[run.status]?.badge">{{ STATUS[run.status]?.label ?? run.status }}</span>
                                </p>
                                <p class="truncate text-xs text-(--app-muted)">
                                    {{ TRIGGERS[run.trigger] ?? run.trigger }}<template v-if="run.user"> · {{ run.user }}</template>
                                    <template v-if="run.file"> · <span class="font-log">{{ run.file }}</span></template>
                                </p>
                                <p v-if="run.message" class="mt-1 whitespace-pre-line text-xs"
                                    :class="run.status === 'failed' ? 'text-(--app-danger)' : 'text-(--app-subtle)'">{{ run.message }}</p>
                            </div>
                            <div class="shrink-0 text-right text-xs text-(--app-subtle)">
                                <p>{{ relativeTime(run.created_at) }}</p>
                                <p v-if="duration(run)" class="font-log">{{ duration(run) }}</p>
                            </div>
                        </li>
                    </ul>
                    <p v-else class="px-5 py-8 text-center text-sm text-(--app-muted)">Noch keine Vorgänge.</p>
                </section>

                <!-- Kommandozeile -->
                <aside class="ui-card p-5 xl:col-span-4">
                    <p class="ui-kicker">Kommandozeile & Cron</p>
                    <p class="ui-muted mt-1 text-sm">
                        Automatische Backups laufen unabhängig von dieser Seite. Im Notfall – z. B. wenn die Website
                        nicht erreichbar ist – funktioniert alles auch über die Kommandozeile.
                    </p>
                    <ul class="mt-4 space-y-2.5 text-xs">
                        <li v-for="command in COMMANDS" :key="command.cmd">
                            <code class="font-log block rounded-lg bg-(--app-surface-2) px-2.5 py-1.5 text-(--app-ink)">{{ command.cmd }}</code>
                            <span class="mt-0.5 block text-(--app-muted)">{{ command.desc }}</span>
                        </li>
                    </ul>
                    <p class="mt-4 text-xs text-(--app-subtle)">Einrichtung, Zweitserver und Notfall-Wiederherstellung: <span class="font-log">docs/backup.md</span></p>
                </aside>
            </div>
        </div>

        <!-- Produktive Wiederherstellung -->
        <Modal :show="restoring !== null" max-width="lg" @close="closeRestore">
            <form v-if="restoring" class="p-6 md:p-7" @submit.prevent="submitRestore">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-danger-soft) text-(--app-danger)">
                    <ArrowUturnLeftIcon class="size-6" />
                </span>
                <h2 class="font-display mt-4 text-3xl">Produktiv wiederherstellen?</h2>
                <p class="ui-muted mt-2 text-sm">
                    Alle aktuellen Daten werden durch den Stand vom
                    <strong class="text-(--app-ink)">{{ formatDateTime(restoring.created_at) }}</strong> ersetzt – User, Fänge,
                    Fotos und Wiki. Alles, was danach eingetragen wurde, geht verloren.
                </p>
                <ul class="mt-4 space-y-1.5 text-sm text-(--app-muted)">
                    <li class="flex gap-2"><CheckCircleIcon class="mt-0.5 size-4 shrink-0 text-(--app-success)" /> Vorher wird automatisch ein Sicherheits-Backup des aktuellen Stands erstellt.</li>
                    <li class="flex gap-2"><ClockIcon class="mt-0.5 size-4 shrink-0" /> Während der Wiederherstellung ist die Website für alle gesperrt (meist wenige Minuten).</li>
                    <li class="flex gap-2"><ArrowRightOnRectangleIcon class="mt-0.5 size-4 shrink-0" /> Danach sind alle abgemeldet – auch du.</li>
                </ul>

                <label for="restore-confirm" class="mt-5 block text-sm font-medium text-(--app-ink)">
                    Zum Bestätigen <code class="font-log rounded bg-(--app-surface-2) px-1.5 py-0.5">{{ config.confirm_word }}</code> eintippen
                </label>
                <input id="restore-confirm" v-model="restoreForm.confirm" type="text" autocomplete="off" spellcheck="false"
                    class="form-input font-log mt-1.5 w-full" :class="{ 'border-(--app-danger)!': restoreForm.errors.confirm }" />
                <p v-if="restoreForm.errors.confirm" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ restoreForm.errors.confirm }}</p>

                <label class="mt-4 flex cursor-pointer items-start gap-2.5 text-sm">
                    <input v-model="restoreForm.acknowledge" type="checkbox" class="form-checkbox mt-0.5" />
                    <span>Mir ist bewusst, dass alle aktuellen Daten ersetzt werden.</span>
                </label>
                <p v-if="restoreForm.errors.acknowledge" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ restoreForm.errors.acknowledge }}</p>

                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <VButton variant="secondary" @click="closeRestore">Abbrechen</VButton>
                    <VButton type="submit" variant="danger" :loading="restoreForm.processing"
                        :disabled="restoreForm.processing || restoreForm.confirm.trim().toUpperCase() !== config.confirm_word || !restoreForm.acknowledge">
                        Jetzt wiederherstellen
                    </VButton>
                </div>
            </form>
        </Modal>
    </PageWrapper>
</template>

<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VButton from '@/components/VButton.vue'
import Modal from '@/JetstreamComponents/Modal.vue'
import { confirmDialog } from '@/composables/useDialog'
import { relativeTime } from '@/utils/format'
import {
    ArrowDownTrayIcon, ArrowPathIcon, CheckCircleIcon, CircleStackIcon, ClockIcon, ExclamationTriangleIcon, LockClosedIcon,
    ServerStackIcon, ShieldCheckIcon, TrashIcon, ArrowUpTrayIcon, ArrowUturnLeftIcon, ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    backups: { type: Array, default: () => [] },
    runs: { type: Array, default: () => [] },
    busy: { type: Boolean, default: false },
    config: { type: Object, required: true },
})

const ACTIONS = {
    create: 'Backup erstellt',
    verify: 'Backup geprüft',
    transfer: 'Auf Zweitserver übertragen',
    'restore-test': 'Wiederherstellungstest',
    'restore-production': 'Produktive Wiederherstellung',
    prune: 'Aufgeräumt',
}
const STATUS = {
    queued: { label: 'eingeplant', badge: '', dot: 'bg-(--app-subtle)' },
    running: { label: 'läuft', badge: 'ui-badge-accent', dot: 'bg-(--app-accent) animate-pulse' },
    success: { label: 'erfolgreich', badge: 'ui-badge-success', dot: 'bg-(--app-success)' },
    warning: { label: 'mit Hinweisen', badge: 'ui-badge-float', dot: 'bg-(--app-float)' },
    failed: { label: 'fehlgeschlagen', badge: 'ui-badge-danger', dot: 'bg-(--app-danger)' },
}
const TRIGGERS = { cli: 'Kommandozeile', schedule: 'Zeitplan', web: 'Weboberfläche' }

const COMMANDS = [
    { cmd: 'php artisan backup:run', desc: 'Erstellen, prüfen, übertragen, aufräumen (für Cron)' },
    { cmd: 'php artisan backup:verify [datei]', desc: 'Vollständig prüfen' },
    { cmd: 'php artisan backup:restore-test [datei]', desc: 'In isolierte Test-Datenbank einspielen und kontrollieren' },
    { cmd: 'php artisan backup:restore-production <datei>', desc: 'Notfall: produktiv wiederherstellen (mit Bestätigung)' },
    { cmd: 'php artisan backup:doctor', desc: 'Voraussetzungen und Zweitserver prüfen' },
]

const configCards = computed(() => {
    const c = props.config
    const r = c.retention
    return [
        {
            label: 'Verschlüsselung', icon: LockClosedIcon, ok: c.encrypted,
            value: c.encrypted ? 'AES-256 aktiv' : 'Nicht verschlüsselt',
            detail: c.encrypted ? 'Passwort in BACKUP_ENCRYPTION_PASSWORD' : 'BACKUP_ENCRYPTION_PASSWORD setzen',
        },
        {
            label: 'Zweitserver', icon: ServerStackIcon, ok: !!c.remote && c.remote.fingerprint,
            value: c.remote ? c.remote.host : 'Nicht konfiguriert',
            detail: c.remote ? `${c.remote.path}${c.remote.fingerprint ? '' : ' · Host-Fingerprint fehlt'}` : 'Backups liegen nur auf diesem Server',
        },
        {
            label: 'Aufbewahrung', icon: TrashIcon, ok: true,
            value: c.remote && !c.remote.prune ? 'Täglich · Sonntag · Monatlich' : `${r.remote.daily} T · ${r.remote.weekly} W · ${r.remote.monthly} M`,
            detail: r.local.keep_latest === 0 ? 'Hier: keines – nach Übertragung gelöscht' : `Hier: die letzten ${r.local.keep_latest}`,
        },
        {
            label: 'Zeitplan', icon: ClockIcon, ok: true,
            value: c.schedule ? `Täglich ${c.schedule} Uhr` : 'Per Cron',
            detail: c.schedule ? 'Laravel-Scheduler' : 'Eigener Cronjob (siehe docs/backup.md)',
        },
    ]
})

const formatSize = (bytes) => `${(bytes / 1048576).toLocaleString('de-AT', { maximumFractionDigits: 1 })} MB`
const formatDateTime = (date) => new Date(date).toLocaleString('de-AT', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
const duration = (run) => {
    if (!run.started_at || !run.finished_at) return null
    const seconds = Math.max(0, Math.round((new Date(run.finished_at) - new Date(run.started_at)) / 1000))
    return seconds < 60 ? `${seconds} s` : `${Math.floor(seconds / 60)} min ${seconds % 60} s`
}

const creating = ref(false)
const visit = (url, onFinish) => router.post(url, {}, { preserveScroll: true, onFinish })

function createBackup() {
    creating.value = true
    visit(route('admin.development.backups.store'), () => { creating.value = false })
}

function verify(backup) {
    visit(route('admin.development.backups.verify', backup.name))
}

async function restoreTest(backup) {
    const confirmed = await confirmDialog({
        title: 'Wiederherstellung testen?',
        message: `${backup.name} wird in die isolierte Test-Datenbank „${props.config.test_database}“ eingespielt und geprüft (Tabellen, Datensätze, Beziehungen, Dateien). Die produktive Datenbank bleibt unberührt; die Test-Datenbank wird danach wieder gelöscht.`,
        confirmText: 'Test starten',
        tone: 'primary',
    })
    if (confirmed) {
        visit(route('admin.development.backups.restore-test', backup.name))
    }
}

/* Hochladen in Teilstücken (umgeht PHP-/nginx-Upload-Limits) */
const fileInput = ref(null)
const upload = reactive({ active: false, name: '', percent: 0, error: '', done: false })

async function startUpload(event) {
    const file = event.target.files?.[0]
    event.target.value = ''
    if (!file) return

    Object.assign(upload, { active: false, name: file.name, percent: 0, error: '', done: false })

    if (!/^petrilog-backup-\d{4}-\d{2}-\d{2}_\d{6}\.zip$/.test(file.name)) {
        upload.error = 'Nur PetriLog-Backups (petrilog-backup-JJJJ-MM-TT_HHMMSS.zip) können hochgeladen werden.'
        return
    }
    if (file.size > props.config.upload.max_mb * 1048576) {
        upload.error = `Die Datei ist größer als ${props.config.upload.max_mb} MB.`
        return
    }

    const chunkSize = props.config.upload.chunk_mb * 1048576
    const total = Math.max(1, Math.ceil(file.size / chunkSize))
    const uploadId = crypto.randomUUID()
    upload.active = true

    try {
        for (let index = 0; index < total; index++) {
            const data = new FormData()
            data.append('upload_id', uploadId)
            data.append('name', file.name)
            data.append('index', index)
            data.append('total', total)
            data.append('chunk', file.slice(index * chunkSize, (index + 1) * chunkSize), file.name)

            await axios.post(route('admin.development.backups.upload'), data, { headers: { Accept: 'application/json' } })
            upload.percent = Math.round(((index + 1) / total) * 100)
        }
        upload.done = true
        router.reload({ only: ['backups', 'runs', 'busy'] })
    } catch (error) {
        upload.error = error.response?.status === 423
            ? 'Passwort-Bestätigung abgelaufen – bitte Seite neu laden.'
            : (error.response?.data?.message ?? 'Hochladen fehlgeschlagen. Bitte erneut versuchen.')
    } finally {
        upload.active = false
    }
}

/* Produktive Wiederherstellung */
const restoring = ref(null)
const restoreForm = useForm({ confirm: '', acknowledge: false })

async function removeBackup(backup) {
    const confirmed = await confirmDialog({
        title: 'Backup löschen?',
        message: `${backup.name} wird von diesem Server gelöscht.${backup.transferred ? ' Die Kopie auf dem Zweitserver bleibt erhalten.' : ''}`,
        confirmText: 'Löschen',
        tone: 'danger',
    })
    if (confirmed) {
        router.delete(route('admin.development.backups.destroy', backup.name), { preserveScroll: true })
    }
}

function openRestore(backup) {
    restoreForm.reset()
    restoreForm.clearErrors()
    restoring.value = backup
}

function closeRestore() {
    restoring.value = null
}

function submitRestore() {
    restoreForm.post(route('admin.development.backups.restore-production', restoring.value.name), {
        preserveScroll: true,
        onSuccess: closeRestore,
    })
}

// Während ein Vorgang läuft, regelmäßig den Status nachladen
let timer = null
watch(() => props.busy, (busy) => {
    clearInterval(timer)
    if (busy) {
        timer = setInterval(() => router.reload({ only: ['backups', 'runs', 'busy'] }), 4000)
    }
}, { immediate: true })
onBeforeUnmount(() => clearInterval(timer))
</script>
