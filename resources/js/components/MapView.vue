<template>
    <div ref="el" class="relative z-0 h-[360px] w-full overflow-hidden rounded-2xl bg-(--app-surface-2)"
        role="img" :aria-label="`Karte: ${title}`"></div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { baseLayers, loadLeaflet, pinIcon } from '@/services/leaflet'

/**
 * Zeigt einen Punkt auf der Karte (basemap.at / OpenStreetMap, ohne API-Key und ohne Cookies).
 */
const props = defineProps({
    latitude: { type: [String, Number], required: true },
    longitude: { type: [String, Number], required: true },
    title: { type: String, default: 'Markierter Punkt' },
    zoom: { type: Number, default: 15 },
    // Startansicht: Karte | Luftbild | OpenStreetMap
    baseLayer: { type: String, default: 'Karte' },
})

const el = ref(null)
let L = null
let map = null
let marker = null

const position = () => [parseFloat(props.latitude), parseFloat(props.longitude)]
const valid = () => position().every(Number.isFinite)

function render() {
    if (!map || !valid()) return
    map.setView(position(), map.getZoom() ?? props.zoom)
    if (marker) {
        marker.setLatLng(position())
    } else {
        marker = L.marker(position(), { icon: pinIcon(L), title: props.title, keyboard: false }).addTo(map)
    }
}

onMounted(async () => {
    L = await loadLeaflet()
    if (!el.value) return

    const layers = baseLayers(L)
    map = L.map(el.value, {
        center: valid() ? position() : [47.6, 13.8],
        zoom: props.zoom,
        layers: [layers[props.baseLayer] ?? layers.Karte],
        // Auf dem Handy scrollt die Seite weiter, verschoben wird mit zwei Fingern
        scrollWheelZoom: false,
    })
    L.control.layers(layers, null, { position: 'topright' }).addTo(map)
    render()
})

watch(() => [props.latitude, props.longitude], render)

onBeforeUnmount(() => map?.remove())
</script>
