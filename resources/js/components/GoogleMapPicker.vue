<template>
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
        {{ label }}
    </label>
    <div :id="id" class="w-full h-[500px]" ref="mapElement"></div>
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
const fallbackCoords = { lat: 47.0707, lng: 15.4395 }

// Map-Initialisierung
const initMap = (lat, lng) => {
    map = new google.maps.Map(mapElement.value, {
        center: { lat, lng },
        zoom: 18,
        mapId: '6da85ff10ebc18655d496f80'
    })

    const { AdvancedMarkerElement } = google.maps.marker

    if (lat && lng) {
        marker = new AdvancedMarkerElement({
            position: { lat, lng },
            map
        })
    }

    map.addListener('click', (event) => {
        const clickedLat = event.latLng.lat()
        const clickedLng = event.latLng.lng()
        if (marker) marker.map = null
        marker = new AdvancedMarkerElement({
            position: { lat: clickedLat, lng: clickedLng },
            map
        })
        const geocoder = new google.maps.Geocoder()
        geocoder.geocode({ location: { lat: clickedLat, lng: clickedLng } }, (results, status) => {
            const address = (status === 'OK' && results[0]) ? results[0].formatted_address : null
            emit('locationSelected', { lat: clickedLat, lng: clickedLng, address })
        })
    })
}

// Google Maps dynamisch laden
const loadGoogleMaps = () => {
    return new Promise((resolve, reject) => {
        if (window.google && window.google.maps) {
            resolve(window.google.maps)
            return
        }

        // Script nur einmal anhängen
        if (document.getElementById("google-maps")) {
            window._initMapCallback = () => resolve(window.google.maps)
            return
        }

        window._initMapCallback = () => resolve(window.google.maps)

        const script = document.createElement("script")
        script.id = "google-maps"
        script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyDz9ywPxkkW1oOy70Rab2oqnhF02DLe5MA&libraries=marker&callback=_initMapCallback"
        script.async = true
        script.defer = true
        script.onerror = reject
        document.head.appendChild(script)
    })
}

onMounted(async () => {
    try {
        await loadGoogleMaps()

        if (props.initialLat && props.initialLng) {
            initMap(props.initialLat, props.initialLng)
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => initMap(position.coords.latitude, position.coords.longitude),
                () => initMap(fallbackCoords.lat, fallbackCoords.lng)
            )
        } else {
            initMap(fallbackCoords.lat, fallbackCoords.lng)
        }
    } catch (err) {
        console.error("Google Maps konnte nicht geladen werden:", err)
        // Fallback: Karte mit Standardkoordinaten initialisieren
        initMap(fallbackCoords.lat, fallbackCoords.lng)
    }
})
</script>