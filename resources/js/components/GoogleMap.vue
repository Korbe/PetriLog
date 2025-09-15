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
let marker = null

// Prüfen ob Gerät iOS/macOS
const isApple = /iPad|iPhone|Macintosh/.test(navigator.userAgent)

const waitForGoogleMaps = (retries = 20) => {
    return new Promise((resolve, reject) => {
        const check = () => {
            if (window.google && window.google.maps) {
                resolve(window.google.maps)
            } else if (retries > 0) {
                setTimeout(() => check(--retries), 300)
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
        ...(isApple ? {} : { mapId: '6da85ff10ebc18655d496f80' }) // mapId nur für Android/Windows
    })

    if (!isApple && window.google.maps.marker?.AdvancedMarkerElement) {
        // ✅ AdvancedMarkerElement für Nicht-Apple
        const { AdvancedMarkerElement } = window.google.maps.marker
        marker = new AdvancedMarkerElement({
            position: { lat, lng },
            map: gmap,
            title: props.title,
        })
    } else {
        // ❌ Klassischer Marker für Apple Geräte
        marker = new window.google.maps.Marker({
            position: { lat, lng },
            map: gmap,
            title: props.title,
            optimized: false
        })
    }

    window.google.maps.event.addListenerOnce(gmap, 'idle', () => {
        window.google.maps.event.trigger(gmap, 'resize')
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
