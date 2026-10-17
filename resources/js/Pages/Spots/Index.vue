<template>
    <PageWrapper title="Spot-Karte" eyebrow="Nur für dich sichtbar">
        <template v-slot:actions>
            <VButton variant="secondary" :loading="locating" @click="locate">
                <ViewfinderCircleIcon class="size-4" /> Mein Standort
            </VButton>
            <VButton :variant="placing ? 'secondary' : 'primary'" @click="togglePlacing">
                <template v-if="placing"><XMarkIcon class="size-4" /> Abbrechen</template>
                <template v-else><MapPinIcon class="size-4" /> Spot setzen</template>
            </VButton>
        </template>

        <div class="grid gap-5 md:gap-6 lg:grid-cols-12">

            <!-- Map -->
            <section class="ui-card overflow-hidden lg:col-span-8" aria-label="Karte">
                <div class="flex flex-wrap items-center gap-2 border-b border-(--app-border) p-3 md:p-4">
                    <div class="flex gap-1 rounded-full bg-(--app-surface-2) p-1" role="radiogroup" aria-label="Darstellung">
                        <button v-for="option in modes" :key="option.value" type="button" role="radio"
                            :aria-checked="mode === option.value"
                            class="flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-sm font-semibold transition"
                            :class="mode === option.value ? 'bg-(--app-surface) text-(--app-ink) shadow-(--app-shadow)' : 'text-(--app-muted) hover:text-(--app-ink)'"
                            @click="mode = option.value">
                            <component :is="option.icon" class="size-4" /> {{ option.label }}
                        </button>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-(--app-border) px-3.5 py-1.5 text-sm font-semibold transition hover:bg-(--app-hover)">
                        <input v-model="showSpots" type="checkbox" class="form-checkbox" />
                        Meine Spots
                    </label>

                    <div class="ml-auto flex w-full gap-2 sm:w-auto">
                        <select v-model="fishFilter" class="form-select min-w-0 flex-1 rounded-full! py-1.5! text-sm sm:w-44" aria-label="Fischart filtern">
                            <option value="">Alle Fischarten</option>
                            <option v-for="fish in fishOptions" :key="fish.id" :value="fish.id">{{ fish.name }}</option>
                        </select>
                        <select v-model="yearFilter" class="form-select min-w-0 rounded-full! py-1.5! text-sm sm:w-32" aria-label="Jahr filtern">
                            <option value="">Alle Jahre</option>
                            <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                </div>

                <div class="relative">
                    <div ref="mapElement" class="petri-map h-[62dvh] min-h-[420px] w-full bg-(--app-surface-2) lg:h-[70dvh]"
                        :class="{ 'is-placing': placing }"></div>

                    <div v-if="!mapReady" class="absolute inset-0 flex items-center justify-center bg-(--app-surface-2)">
                        <span class="size-9 animate-spin rounded-full border-4 border-(--app-border) border-t-primary-500"></span>
                    </div>

                    <transition name="fade">
                        <div v-if="placing" class="pointer-events-none absolute inset-x-0 top-3 z-[500] flex justify-center px-3">
                            <p class="rounded-full bg-[#06212b]/90 px-4 py-2 text-sm font-semibold text-white shadow-lg backdrop-blur">
                                Tippe auf die Karte, um den Spot zu setzen
                            </p>
                        </div>
                    </transition>

                    <!-- Legend -->
                    <div v-if="mapReady && mode === 'markers'"
                        class="pointer-events-none absolute bottom-3 left-3 z-[500] flex flex-wrap gap-x-3 gap-y-1 rounded-2xl bg-(--app-surface)/90 px-3 py-2 text-xs font-medium text-(--app-muted) shadow-(--app-shadow) backdrop-blur">
                        <span class="flex items-center gap-1.5"><span class="petri-dot is-legend is-kept"></span>Entnommen</span>
                        <span class="flex items-center gap-1.5"><span class="petri-dot is-legend is-released"></span>Zurückgesetzt</span>
                        <span class="flex items-center gap-1.5"><span class="petri-dot is-legend"></span>Ohne Angabe</span>
                    </div>
                </div>
            </section>

            <!-- Side panel -->
            <aside class="space-y-5 lg:col-span-4">
                <dl class="grid grid-cols-3 gap-3">
                    <div v-for="stat in stats" :key="stat.label" class="ui-card px-3 py-4 text-center">
                        <dt class="text-[11px] text-(--app-subtle)">{{ stat.label }}</dt>
                        <dd class="font-display mt-1 text-4xl leading-none">{{ stat.value }}</dd>
                    </div>
                </dl>

                <section v-if="hotspots.length" class="ui-card p-5">
                    <p class="ui-kicker">Deine Hotspots</p>
                    <p class="ui-muted mt-1 text-sm">Wo du im Umkreis von rund einem Kilometer am meisten fängst.</p>
                    <ol class="mt-3 space-y-1">
                        <li v-for="(spot, index) in hotspots" :key="spot.key">
                            <button type="button" class="ui-row w-full text-left" @click="flyTo(spot.lat, spot.lng, 15)">
                                <span class="font-display flex size-9 shrink-0 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--app-float)_14%,transparent)] text-xl text-(--app-float)">
                                    {{ index + 1 }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold">{{ spot.water ?? 'Unbekanntes Gewässer' }}</span>
                                    <span class="block truncate text-xs text-(--app-muted)">{{ spot.topFish }}</span>
                                </span>
                                <span class="font-log text-sm text-(--app-ink)">{{ spot.count }}×</span>
                            </button>
                        </li>
                    </ol>
                </section>

                <section class="ui-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="ui-kicker">Meine Spots</p>
                        <span class="ui-badge">{{ spots.length }}</span>
                    </div>

                    <ul v-if="spots.length" class="mt-3 max-h-80 space-y-1 overflow-y-auto">
                        <li v-for="spot in spots" :key="spot.id" class="flex items-center gap-1">
                            <button type="button" class="ui-row min-w-0 flex-1 text-left" @click="flyTo(spot.latitude, spot.longitude, 16, spot.id)">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                                    <MapPinIcon class="size-4" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold">{{ spot.name }}</span>
                                    <span v-if="spot.note" class="block truncate text-xs text-(--app-muted)">{{ spot.note }}</span>
                                </span>
                            </button>
                            <button type="button" class="ui-icon-btn shrink-0" :aria-label="`${spot.name} bearbeiten`" @click="openDialog(spot)">
                                <PencilSquareIcon class="size-4" />
                            </button>
                        </li>
                    </ul>
                    <div v-else class="ui-panel mt-3 px-4 py-5 text-sm text-(--app-muted)">
                        Merk dir deine besten Plätze: Tippe auf <strong class="text-(--app-ink)">Spot setzen</strong> und dann
                        auf die Karte – oder speichere direkt deinen aktuellen Standort.
                    </div>
                </section>

                <div class="ui-panel flex items-start gap-3 px-4 py-3 text-sm text-(--app-muted)">
                    <LockClosedIcon class="mt-0.5 size-5 shrink-0 text-(--app-accent-ink)" />
                    <span>Spots und diese Karte sind privat. Beim Teilen eines Fangs wird nur dessen eigener Ort gezeigt – nie deine Spots.</span>
                </div>
            </aside>
        </div>

        <!-- Spot dialog -->
        <Modal :show="dialogOpen" max-width="md" @close="closeDialog">
            <form class="p-6" @submit.prevent="saveSpot">
                <span class="flex size-12 items-center justify-center rounded-2xl bg-(--app-accent-soft) text-(--app-accent-ink)">
                    <MapPinIcon class="size-6" />
                </span>
                <h2 class="font-display mt-4 text-3xl">{{ form.id ? 'Spot bearbeiten' : 'Neuer Spot' }}</h2>
                <p class="font-log mt-1 text-xs text-(--app-subtle)">{{ formatCoords(form.latitude, form.longitude) }}</p>

                <div class="mt-5 space-y-4">
                    <VInput id="spot-name" v-model="form.name" label="Name" mandatory placeholder="z. B. Schilfkante Nordufer"
                        :error="form.errors.name" />
                    <div>
                        <VTextarea id="spot-note" v-model="form.note" label="Notiz" :rows="3"
                            placeholder="Tiefe, Köder, Parkplatz, beste Uhrzeit …" />
                        <div v-if="form.errors.note" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ form.errors.note }}</div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-2">
                    <VButton v-if="form.id" type="button" variant="ghost" class="text-(--app-danger)!" @click="deleteSpot">
                        <TrashIcon class="size-4" /> Löschen
                    </VButton>
                    <div class="ml-auto flex gap-2">
                        <VButton type="button" variant="secondary" @click="closeDialog">Abbrechen</VButton>
                        <VButton type="submit" :loading="form.processing" :disabled="form.processing">
                            <CheckIcon class="size-4" /> Speichern
                        </VButton>
                    </div>
                </div>
            </form>
        </Modal>
    </PageWrapper>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, shallowRef, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import VTextarea from '@/components/VTextarea.vue'
import Modal from '@/JetstreamComponents/Modal.vue'
import { AUSTRIA_CENTER, baseLayers, loadLeaflet } from '@/services/leaflet'
import { alertDialog, confirmDialog } from '@/composables/useDialog'
import {
    MapPinIcon, ViewfinderCircleIcon, XMarkIcon, PencilSquareIcon, TrashIcon, CheckIcon, LockClosedIcon, FireIcon,
    MapIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    catches: { type: Array, default: () => [] },
    spots: { type: Array, default: () => [] },
})

/* ---------------- Filter & view state ---------------- */
const modes = [
    { value: 'markers', label: 'Fänge', icon: MapIcon },
    { value: 'heat', label: 'Heatmap', icon: FireIcon },
]
const mode = ref('markers')
const showSpots = ref(true)
const fishFilter = ref('')
const yearFilter = ref('')

const yearOf = (date) => new Date(date).getFullYear()

const fishOptions = computed(() => {
    const seen = new Map()
    props.catches.forEach(c => c.fishId && seen.set(c.fishId, c.fish))
    return [...seen].map(([id, name]) => ({ id, name })).sort((a, b) => a.name.localeCompare(b.name, 'de'))
})
const yearOptions = computed(() => [...new Set(props.catches.map(c => yearOf(c.date)))].sort((a, b) => b - a))

const visibleCatches = computed(() => props.catches.filter(c =>
    (!fishFilter.value || c.fishId === fishFilter.value)
    && (!yearFilter.value || yearOf(c.date) === yearFilter.value)
))

const stats = computed(() => [
    { label: 'Fänge', value: visibleCatches.value.length },
    { label: 'Spots', value: props.spots.length },
    { label: 'Gewässer', value: new Set(visibleCatches.value.map(c => c.water).filter(Boolean)).size },
])

// Fänge im ~1-km-Raster zusammenfassen
const hotspots = computed(() => {
    const cells = new Map()
    visibleCatches.value.forEach(c => {
        const key = `${c.lat.toFixed(2)},${c.lng.toFixed(2)}`
        const cell = cells.get(key) ?? { key, items: [] }
        cell.items.push(c)
        cells.set(key, cell)
    })
    const mostCommon = (values) => {
        const counts = values.filter(Boolean).reduce((acc, v) => acc.set(v, (acc.get(v) ?? 0) + 1), new Map())
        return [...counts].sort((a, b) => b[1] - a[1])[0]?.[0] ?? null
    }
    return [...cells.values()]
        .filter(cell => cell.items.length >= 2)
        .sort((a, b) => b.items.length - a.items.length)
        .slice(0, 3)
        .map(cell => ({
            key: cell.key,
            count: cell.items.length,
            lat: cell.items.reduce((s, c) => s + c.lat, 0) / cell.items.length,
            lng: cell.items.reduce((s, c) => s + c.lng, 0) / cell.items.length,
            water: mostCommon(cell.items.map(c => c.water)),
            topFish: mostCommon(cell.items.map(c => c.fish)),
        }))
})

/* ---------------- Map ---------------- */
const mapElement = ref(null)
const mapReady = ref(false)
const placing = ref(false)
const locating = ref(false)

let L = null
const map = shallowRef(null)
let catchLayer = null
let heatLayer = null
let spotLayer = null
let positionMarker = null
const spotMarkers = new Map()

const el = (tag, className, text) => {
    const node = document.createElement(tag)
    if (className) node.className = className
    if (text !== undefined && text !== null) node.textContent = text
    return node
}

const formatDate = (date) => new Date(date).toLocaleDateString('de-AT', { day: '2-digit', month: '2-digit', year: 'numeric' })
const formatCoords = (lat, lng) => lat == null ? '' : `${Number(lat).toFixed(5)}° N · ${Number(lng).toFixed(5)}° E`

// Popup-Inhalte werden als DOM gebaut (textContent), damit Namen nie als HTML interpretiert werden
function catchPopup(c) {
    const root = el('div', 'petri-popup')
    if (c.photo) {
        const img = el('img', 'petri-popup-photo')
        img.src = c.photo
        img.alt = ''
        img.loading = 'lazy'
        root.append(img)
    }
    root.append(el('p', 'petri-popup-title', c.fish))
    const facts = [`${c.length} cm`, c.weight ? `${c.weight} g` : null, formatDate(c.date)].filter(Boolean).join(' · ')
    root.append(el('p', 'petri-popup-meta', facts))
    if (c.water) root.append(el('p', 'petri-popup-meta', c.water))
    const link = el('a', 'petri-popup-link', 'Fang ansehen →')
    link.href = `/catched/${c.id}`
    link.addEventListener('click', (e) => { e.preventDefault(); router.visit(link.href) })
    root.append(link)
    return root
}

function spotPopup(spot) {
    const root = el('div', 'petri-popup')
    root.append(el('p', 'petri-popup-kicker', 'Mein Spot'))
    root.append(el('p', 'petri-popup-title', spot.name))
    if (spot.note) root.append(el('p', 'petri-popup-note', spot.note))
    const actions = el('div', 'petri-popup-actions')
    const edit = el('button', 'petri-popup-link', 'Bearbeiten')
    edit.type = 'button'
    edit.addEventListener('click', () => openDialog(spot))
    const route = el('a', 'petri-popup-link', 'Navigation')
    route.href = `https://www.google.com/maps/dir/?api=1&destination=${spot.latitude},${spot.longitude}`
    route.target = '_blank'
    route.rel = 'noopener'
    actions.append(edit, route)
    root.append(actions)
    return root
}

const catchIcon = (c) => L.divIcon({
    className: '',
    html: `<span class="petri-dot ${c.released === true ? 'is-released' : c.released === false ? 'is-kept' : ''}"></span>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9],
    popupAnchor: [0, -8],
})

const spotIcon = () => L.divIcon({
    className: '',
    html: '<span class="petri-spot-pin"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5Z"/></svg></span>',
    iconSize: [34, 34],
    iconAnchor: [17, 32],
    popupAnchor: [0, -28],
})

function renderCatches() {
    if (!map.value) return
    catchLayer?.remove()
    heatLayer?.remove()
    catchLayer = null
    heatLayer = null

    if (mode.value === 'heat') {
        heatLayer = L.heatLayer(visibleCatches.value.map(c => [c.lat, c.lng, 1]), {
            radius: 30,
            blur: 20,
            minOpacity: 0.45,
            // Ab dieser Zoomstufe zählt jeder Fang voll – sonst verblassen einzelne Fänge beim Hineinzoomen
            maxZoom: 9,
            gradient: { 0.2: '#118df0', 0.5: '#2ec5b6', 0.75: '#ffb020', 1: '#ff5a1f' },
        }).addTo(map.value)
        return
    }

    catchLayer = L.markerClusterGroup({
        showCoverageOnHover: false,
        maxClusterRadius: 44,
        iconCreateFunction: (cluster) => L.divIcon({
            className: '',
            html: `<span class="petri-cluster">${cluster.getChildCount()}</span>`,
            iconSize: [40, 40],
        }),
    })
    visibleCatches.value.forEach(c => {
        L.marker([c.lat, c.lng], { icon: catchIcon(c), title: c.fish })
            .bindPopup(() => catchPopup(c), { minWidth: 200, maxWidth: 240 })
            .addTo(catchLayer)
    })
    catchLayer.addTo(map.value)
}

function renderSpots() {
    if (!map.value) return
    spotLayer?.remove()
    spotMarkers.clear()
    if (!showSpots.value) return

    spotLayer = L.layerGroup()
    props.spots.forEach(spot => {
        const marker = L.marker([spot.latitude, spot.longitude], { icon: spotIcon(), title: spot.name, zIndexOffset: 1000 })
            .bindPopup(() => spotPopup(spot), { minWidth: 200, maxWidth: 260 })
            .addTo(spotLayer)
        spotMarkers.set(spot.id, marker)
    })
    spotLayer.addTo(map.value)
}

function fitToData() {
    const points = [
        ...visibleCatches.value.map(c => [c.lat, c.lng]),
        ...(showSpots.value ? props.spots.map(s => [s.latitude, s.longitude]) : []),
    ]
    if (points.length) {
        map.value.fitBounds(L.latLngBounds(points), { padding: [40, 40], maxZoom: 15 })
    } else {
        map.value.setView(AUSTRIA_CENTER, 7)
    }
}

function flyTo(lat, lng, zoom = 15, spotId = null) {
    if (!map.value) return
    map.value.flyTo([lat, lng], zoom, { duration: 0.8 })
    if (spotId && spotMarkers.has(spotId)) {
        map.value.once('moveend', () => spotMarkers.get(spotId)?.openPopup())
    }
    // Auf dem Handy liegt die Karte über der Liste
    mapElement.value?.scrollIntoView({ behavior: 'smooth', block: 'center' })
}

function togglePlacing() {
    placing.value = !placing.value
}

function locate() {
    if (!navigator.geolocation) return
    locating.value = true
    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            locating.value = false
            const latlng = [coords.latitude, coords.longitude]
            positionMarker?.remove()
            const save = el('button', 'petri-popup-link', 'Hier einen Spot speichern')
            save.type = 'button'
            save.addEventListener('click', () => openDialog({ latitude: coords.latitude, longitude: coords.longitude }))
            const content = el('div', 'petri-popup')
            content.append(el('p', 'petri-popup-title', 'Du bist hier'), save)
            positionMarker = L.marker(latlng, {
                icon: L.divIcon({ className: '', html: '<span class="petri-position"></span>', iconSize: [22, 22], iconAnchor: [11, 11] }),
                zIndexOffset: 2000,
            }).bindPopup(content).addTo(map.value)
            map.value.flyTo(latlng, 16, { duration: 0.8 })
            map.value.once('moveend', () => positionMarker?.openPopup())
        },
        (error) => {
            locating.value = false
            alertDialog({
                title: 'Standort nicht verfügbar',
                message: error.code === error.PERMISSION_DENIED
                    ? 'PetriLog darf deinen Standort nicht abrufen. Erlaube den Standortzugriff in den Browser- bzw. Handy-Einstellungen und versuch es erneut.'
                    : 'Dein Standort konnte gerade nicht ermittelt werden – am Wasser hilft es oft, kurz unter freiem Himmel zu warten und es noch einmal zu probieren.',
                tone: 'warning',
            })
        },
        { enableHighAccuracy: true, timeout: 15000 },
    )
}

onMounted(async () => {
    L = await loadLeaflet()
    if (!mapElement.value) return

    const layers = baseLayers(L)
    map.value = L.map(mapElement.value, { zoomControl: false, layers: [layers.Karte] })
    L.control.zoom({ position: 'bottomright', zoomInTitle: 'Hineinzoomen', zoomOutTitle: 'Herauszoomen' }).addTo(map.value)
    L.control.layers(layers, null, { position: 'topright' }).addTo(map.value)
    L.control.scale({ imperial: false, position: 'bottomright' }).addTo(map.value)

    map.value.on('click', (e) => {
        if (!placing.value) return
        placing.value = false
        openDialog({ latitude: e.latlng.lat, longitude: e.latlng.lng })
    })

    renderCatches()
    renderSpots()
    fitToData()
    mapReady.value = true
})

onBeforeUnmount(() => {
    map.value?.remove()
    map.value = null
})

watch([mode, visibleCatches], renderCatches)
watch([showSpots, () => props.spots], renderSpots)
watch([fishFilter, yearFilter], () => map.value && fitToData())

/* ---------------- Spot dialog ---------------- */
const dialogOpen = ref(false)
const form = useForm({ id: null, name: '', note: '', latitude: null, longitude: null })

function openDialog(spot) {
    map.value?.closePopup()
    form.clearErrors()
    form.id = spot.id ?? null
    form.name = spot.name ?? ''
    form.note = spot.note ?? ''
    form.latitude = spot.latitude
    form.longitude = spot.longitude
    dialogOpen.value = true
}

function closeDialog() {
    dialogOpen.value = false
}

function saveSpot() {
    const options = { preserveScroll: true, preserveState: true, onSuccess: closeDialog }
    const data = (d) => ({ name: d.name, note: d.note, latitude: d.latitude, longitude: d.longitude })

    if (form.id) {
        form.transform(data).put(route('app.spots.update', form.id), options)
    } else {
        form.transform(data).post(route('app.spots.store'), options)
    }
}

async function deleteSpot() {
    if (!form.id) return
    const confirmed = await confirmDialog({
        title: 'Spot löschen?',
        message: `„${form.name}“ wird dauerhaft aus deinen Spots entfernt.`,
        confirmText: 'Spot löschen',
        tone: 'danger',
    })
    if (!confirmed) return
    router.delete(route('app.spots.destroy', form.id), { preserveScroll: true, preserveState: true, onSuccess: closeDialog })
}
</script>
