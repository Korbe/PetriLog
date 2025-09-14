<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
        </label>
        <div :id="id" class="w-full h-[500px]" ref="mapElement"></div>
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

// Map initialisieren
const initMap = (lat, lng) => {
    map = new window.google.maps.Map(mapElement.value, {
        center: { lat, lng },
        zoom: 18,
        mapId: '6da85ff10ebc18655d496f80'
    })

    const { AdvancedMarkerElement } = window.google.maps.marker

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

onMounted(() => {
    if (!(window.google && window.google.maps)) {
        alert('Google Maps API wurde nicht geladen.')
        return
    }

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
})
</script>