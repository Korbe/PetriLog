<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
        </label>
        <div :id="id" ref="mapElement" class="w-full h-[500px]" style="min-height: 500px;"></div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

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
const fallbackCoords = { lat: 47.0707, lng: 15.4395 } // Graz als Beispiel

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

// Map initialisieren
const initMap = (lat, lng) => {
    map = new window.google.maps.Map(mapElement.value, {
        center: { lat, lng },
        zoom: 18,
        mapId: '6da85ff10ebc18655d496f80'
    })

    // Klassischer Marker (stabil auf allen Geräten inkl. iPad)
    if (lat && lng) {
        marker = new window.google.maps.Marker({
            position: { lat, lng },
            map,
            title: props.label || 'Ausgewählter Punkt'
        })
    }

    // Klick-Listener → Marker setzen + Geocoding
    map.addListener('click', (event) => {
        const clickedLat = event.latLng.lat()
        const clickedLng = event.latLng.lng()

        if (marker) marker.setMap(null)

        marker = new window.google.maps.Marker({
            position: { lat: clickedLat, lng: clickedLng },
            map
        })

        const geocoder = new window.google.maps.Geocoder()
        geocoder.geocode(
            { location: { lat: clickedLat, lng: clickedLng } },
            (results, status) => {
                const address =
                    status === 'OK' && results[0] ? results[0].formatted_address : null
                emit('locationSelected', { lat: clickedLat, lng: clickedLng, address })
            }
        )
    })
}

onMounted(async () => {
    try {
        await waitForGoogleMaps()

        if (props.initialLat && props.initialLng) {
            initMap(props.initialLat, props.initialLng)
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) =>
                    initMap(position.coords.latitude, position.coords.longitude),
                () => initMap(fallbackCoords.lat, fallbackCoords.lng)
            )
        } else {
            initMap(fallbackCoords.lat, fallbackCoords.lng)
        }
    } catch (err) {
        console.error(err.message)
        initMap(fallbackCoords.lat, fallbackCoords.lng)
    }
})
</script>
