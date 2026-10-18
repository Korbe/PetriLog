<template>
    <div class="relative z-0 w-full overflow-hidden rounded-2xl border border-(--app-border)" style="height:420px; min-height:420px;">
        <div v-if="isLoading" class="absolute inset-0 z-[500] flex flex-col items-center justify-center bg-(--app-surface-2)">
            <div class="size-10 animate-spin rounded-full border-4 border-(--app-border) border-t-primary-500"></div>
            <div class="mt-3 text-sm text-(--app-muted)">Standort wird ermittelt…</div>
        </div>

        <div ref="el" class="h-full w-full bg-(--app-surface-2)" :aria-label="label || 'Karte zur Auswahl der Position'"></div>

        <!-- Hinweis, wenn der Standort nicht ermittelt werden konnte -->
        <div v-if="locateError" role="alert"
            class="absolute inset-x-3 top-3 z-[600] flex items-start gap-2 rounded-2xl bg-(--app-surface) p-3 pr-2 text-xs text-(--app-ink) shadow-(--app-shadow-lg)">
            <ExclamationTriangleIcon class="size-4 shrink-0 text-(--app-float)" />
            <p class="flex-1">{{ locateError }}</p>
            <button type="button" class="-my-1 rounded-full p-1 text-(--app-muted) hover:text-(--app-ink)"
                aria-label="Hinweis schließen" @click="locateError = ''">
                <XMarkIcon class="size-4" />
            </button>
        </div>

        <button type="button" @click="locate" :aria-busy="locating"
            class="absolute bottom-4 right-4 z-[500] inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-(--app-border-strong) bg-(--app-surface) px-4 py-2.5 text-sm font-semibold text-(--app-ink) shadow-(--app-shadow-lg) transition hover:border-primary-500 hover:text-primary-600 active:scale-[0.97] dark:hover:text-primary-300"
            :class="locating ? 'pointer-events-none' : ''">
            <span v-if="locating" class="size-4 animate-spin rounded-full border-2 border-(--app-border-strong) border-t-primary-500"></span>
            <MapPinIcon v-else class="size-4" />
            {{ locating ? 'Standort wird gesucht …' : 'Mein Standort' }}
        </button>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import axios from 'axios'
import { ExclamationTriangleIcon, MapPinIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { baseLayers, loadLeaflet, pinIcon } from '@/services/leaflet'

/**
 * Position wählen: Tippen setzt den Pin, der Pin lässt sich verschieben. Die Adresse wird über den eigenen
 * Server bei OpenStreetMap nachgeschlagen (route app.geocode.reverse).
 */
const props = defineProps({
    label: { type: String, default: '' },
    initialLat: { type: [Number, String], default: null },
    initialLng: { type: [Number, String], default: null },
})

const emit = defineEmits(['locationSelected'])

// Villach als Fallback, wenn kein Standort verfügbar ist
const FALLBACK = [46.6103, 13.8558]

const el = ref(null)
const isLoading = ref(true)
const locating = ref(false)
const locateError = ref('')
let L = null
let map = null
let marker = null
let geocodeRequest = 0

const round = (value) => Math.round(value * 1e6) / 1e6

async function select(lat, lng) {
    lat = round(lat)
    lng = round(lng)
    placeMarker(lat, lng)

    // Koordinaten sofort, Adresse sobald bekannt (ältere Antworten werden verworfen)
    const request = ++geocodeRequest
    emit('locationSelected', { lat, lng, address: null })
    try {
        const { data } = await axios.get(route('app.geocode.reverse'), { params: { lat, lng } })
        if (request === geocodeRequest) emit('locationSelected', { lat, lng, address: data.address })
    } catch { /* ohne Adresse weiter */ }
}

function placeMarker(lat, lng) {
    if (marker) {
        marker.setLatLng([lat, lng])
        return
    }
    marker = L.marker([lat, lng], { icon: pinIcon(L), draggable: true, title: props.label || 'Ausgewählter Punkt' }).addTo(map)
    marker.on('dragend', () => {
        const { lat: newLat, lng: newLng } = marker.getLatLng()
        select(newLat, newLng)
    })
}

// Standort über die Geolocation-API des Browsers (GPS/WLAN des Geräts) – ohne API-Key, ohne Kosten
function getPosition(options) {
    return new Promise((resolve, reject) => {
        if (!window.isSecureContext) return reject({ code: 'insecure' })
        if (!navigator.geolocation) return reject({ code: 'unsupported' })
        navigator.geolocation.getCurrentPosition(
            (pos) => resolve([pos.coords.latitude, pos.coords.longitude]),
            reject,
            options,
        )
    })
}

async function currentPosition({ fresh = false } = {}) {
    try {
        return await getPosition({ enableHighAccuracy: true, timeout: 8000, maximumAge: fresh ? 0 : 60000 })
    } catch (error) {
        // GPS zu langsam (z. B. drinnen oder am PC) → grobe Position über WLAN/Netz versuchen
        if (error?.code === 2 || error?.code === 3) {
            return getPosition({ enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 })
        }
        throw error
    }
}

function errorMessage(error) {
    switch (error?.code) {
        case 1: return 'Standortzugriff ist blockiert. Erlaube ihn in den Browser- bzw. App-Einstellungen oder tippe einfach in die Karte.'
        case 'insecure': return 'Der Standort ist nur über eine sichere Verbindung (https) verfügbar. Tippe stattdessen in die Karte.'
        case 'unsupported': return 'Dieser Browser kann keinen Standort ermitteln. Tippe stattdessen in die Karte.'
        default: return 'Standort konnte gerade nicht ermittelt werden (kein GPS-Signal?). Versuch es nochmal oder tippe in die Karte.'
    }
}

async function locate() {
    if (!map || locating.value) return
    locating.value = true
    locateError.value = ''
    try {
        const [lat, lng] = await currentPosition({ fresh: true })
        map.flyTo([lat, lng], 17, { duration: 0.6 })
        select(lat, lng)
    } catch (error) {
        locateError.value = errorMessage(error)
    } finally {
        locating.value = false
    }
}

onMounted(async () => {
    L = await loadLeaflet()
    if (!el.value) return

    const lat = parseFloat(props.initialLat)
    const lng = parseFloat(props.initialLng)
    const hasInitial = Number.isFinite(lat) && Number.isFinite(lng)

    let start = hasInitial ? [lat, lng] : null
    if (!start) {
        start = await currentPosition().catch((error) => {
            locateError.value = errorMessage(error)
            return null
        })
    }

    const layers = baseLayers(L)
    map = L.map(el.value, {
        center: start ?? FALLBACK,
        zoom: start ? 17 : 12,
        layers: [layers.Luftbild],
    })
    L.control.layers(layers, null, { position: 'topright' }).addTo(map)
    map.on('click', (event) => select(event.latlng.lat, event.latlng.lng))

    if (hasInitial) {
        // Bestehenden Fang nur anzeigen – Adresse nicht überschreiben
        placeMarker(lat, lng)
    } else if (start) {
        select(start[0], start[1])
    }

    isLoading.value = false
    // Karte war während des Ladens verdeckt
    setTimeout(() => map?.invalidateSize(), 0)
})

onBeforeUnmount(() => map?.remove())
</script>
