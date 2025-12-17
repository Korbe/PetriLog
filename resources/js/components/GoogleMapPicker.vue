<template>
    <div class="relative w-full" style="height:500px; min-height:500px;">
        <!-- 🔄 Loading Overlay -->
        <div v-if="isLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 z-10">
            <!-- Spinner -->
            <div class="w-10 h-10 border-4 border-gray-300 border-t-blue-500 rounded-full animate-spin"></div>

            <!-- Text -->
            <div class="mt-3 text-sm text-gray-600">
                Standort wird ermittelt…
            </div>
        </div>

        <!-- 🗺️ Map -->
        <div v-show="!isLoading" :id="id" ref="mapElement" class="w-full h-full" style="background:#eee;"></div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isLoading = ref(true)

const props = defineProps({
    id: { type: String, default: 'google-map-picker' },
    label: { type: String, default: '' },
    initialLat: { type: Number, default: null },
    initialLng: { type: Number, default: null }
})

const emit = defineEmits(['locationSelected'])

const mapElement = ref(null)
let map = null
let marker = null

// Villach als Default
const fallbackCoords = { lat: 46.6103, lng: 13.8558 }

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

const initMap = (lat, lng) => {
    map = new window.google.maps.Map(mapElement.value, {
        center: { lat, lng },
        zoom: 18,
        gestureHandling: 'greedy',
        ...(isApple ? {} : { mapId: '6da85ff10ebc18655d496f80' }) // mapId nur für Android/Windows
    })



    if (lat && lng) {
        if (!isApple && window.google.maps.marker?.AdvancedMarkerElement) {
            // ✅ AdvancedMarkerElement für Nicht-Apple
            const { AdvancedMarkerElement } = window.google.maps.marker
            marker = new AdvancedMarkerElement({
                position: { lat, lng },
                map,
                title: props.label || 'Ausgewählter Punkt',
            })
        } else {
            // ❌ Klassischer Marker für iOS/macOS
            marker = new window.google.maps.Marker({
                position: { lat, lng },
                map,
                title: props.label || 'Ausgewählter Punkt',
                optimized: false
            })
        }

        emitLocation(lat, lng);

        isLoading.value = false

        // Resize NACH Sichtbarkeit
        setTimeout(() => {
            window.google.maps.event.trigger(map, 'resize')
        }, 0)
    }

    window.google.maps.event.addListenerOnce(map, 'idle', () => {
        isLoading.value = false
        window.google.maps.event.trigger(map, 'resize')
    })

    map.addListener('click', (event) => {
        const clickedLat = event.latLng.lat()
        const clickedLng = event.latLng.lng()

        if (marker) {
            if (!isApple && marker instanceof window.google.maps.marker.AdvancedMarkerElement) {
                marker.map = null
            } else {
                marker.setMap(null)
            }
        }

        if (!isApple && window.google.maps.marker?.AdvancedMarkerElement) {
            const { AdvancedMarkerElement } = window.google.maps.marker
            marker = new AdvancedMarkerElement({
                position: { lat: clickedLat, lng: clickedLng },
                map,
            })
        } else {
            marker = new window.google.maps.Marker({
                position: { lat: clickedLat, lng: clickedLng },
                map,
                optimized: false
            })
        }

        emitLocation(clickedLat, clickedLng)
    })
}

const emitLocation = (lat, lng) => {
    const geocoder = new window.google.maps.Geocoder()

    geocoder.geocode(
        { location: { lat, lng } },
        (results, status) => {
            const address =
                status === 'OK' && results[0]
                    ? results[0].formatted_address
                    : null

            emit('locationSelected', { lat, lng, address })
        }
    )
}

onMounted(async () => {
    try {
        await waitForGoogleMaps()

        setTimeout(() => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        initMap(pos.coords.latitude, pos.coords.longitude)
                    },
                    () => {
                        if (props.initialLat && props.initialLng) {
                            initMap(props.initialLat, props.initialLng)
                        } else {
                            initMap(fallbackCoords.lat, fallbackCoords.lng)
                        }
                    }
                )
            } else if (props.initialLat && props.initialLng) {
                initMap(props.initialLat, props.initialLng)
            } else {
                initMap(fallbackCoords.lat, fallbackCoords.lng)
            }
        }, 300)
    } catch (err) {
        console.error(err.message)
        initMap(fallbackCoords.lat, fallbackCoords.lng)
    }
})
</script>
