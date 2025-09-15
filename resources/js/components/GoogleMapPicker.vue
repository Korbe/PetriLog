<template>
    <div>
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
            {{ label }}
        </label>
        <div :id="id" ref="mapElement" class="w-full"
            style="height:500px; min-height:500px; display:block; background:#eee;"></div>
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

// 👉 Villach als Default
const fallbackCoords = { lat: 46.6103, lng: 13.8558 }

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
        gestureHandling: 'greedy'
    })

    const { AdvancedMarkerElement } = window.google.maps.marker

    if (lat && lng) {
        marker = new AdvancedMarkerElement({
            position: { lat, lng },
            map,
            title: props.label || 'Ausgewählter Punkt',
        })
    }

    window.google.maps.event.addListenerOnce(map, 'idle', () => {
        window.google.maps.event.trigger(map, 'resize')
    })

    map.addListener('click', (event) => {
        const clickedLat = event.latLng.lat()
        const clickedLng = event.latLng.lng()

        if (marker) marker.map = null // AdvancedMarker entfernen

        marker = new AdvancedMarkerElement({
            position: { lat: clickedLat, lng: clickedLng },
            map,
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
