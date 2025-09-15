<template>
    <div ref="map" class="w-full h-[400px]"></div>
    <!-- rounded-2xl -->
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const props = defineProps({
    latitude: {
        type: [String, Number],
        required: true,
    },
    longitude: {
        type: [String, Number],
        required: true,
    },
    title: {
        type: String,
        default: 'Markierter Punkt',
    },
})

const map = ref(null)
let gmap = null

const waitForGoogleMaps = (retries = 20) => {
    return new Promise((resolve, reject) => {
        const check = () => {
            if (window.google && window.google.maps) {
                resolve(window.google.maps)
            } else if (retries > 0) {
                setTimeout(() => check(--retries), 300) // alle 300ms prüfen
            } else {
                reject(new Error('Google Maps API wurde nicht geladen.'))
            }
        }
        check()
    })
}

const initMap = () => {
    if (!map.value || !props.latitude || !props.longitude) return

    const lat = parseFloat(props.latitude)
    const lng = parseFloat(props.longitude)

    gmap = new window.google.maps.Map(map.value, {
        center: { lat, lng },
        zoom: 16,
        gestureHandling: 'greedy',
        mapId: '6da85ff10ebc18655d496f80',
    })

    const { AdvancedMarkerElement } = window.google.maps.marker

    new AdvancedMarkerElement({
        position: { lat, lng },
        map: gmap,
        title: props.title,
    })
}

onMounted(async () => {
    try {
        await waitForGoogleMaps()
        initMap()
    } catch (err) {
        console.error(err.message)
    }
})

watch(() => [props.latitude, props.longitude], async () => {
    if (window.google && window.google.maps) {
        initMap()
    } else {
        try {
            await waitForGoogleMaps()
            initMap()
        } catch (err) {
            console.error(err.message)
        }
    }
})
</script>
