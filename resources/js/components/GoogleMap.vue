<template>
    <div ref="map" class="w-full h-[400px] rounded-2xl"></div>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue';

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
});

const map = ref(null);
let gmap = null;

const initMap = () => {
    if (!map.value || !props.latitude || !props.longitude) return;

    const lat = parseFloat(props.latitude);
    const lng = parseFloat(props.longitude);

    gmap = new window.google.maps.Map(map.value, {
        center: { lat, lng },
        zoom: 16,
        mapId: '6da85ff10ebc18655d496f80',
    });

    const { AdvancedMarkerElement } = window.google.maps.marker;

    new AdvancedMarkerElement({
        position: { lat, lng },
        map: gmap,
        title: props.title,
    });
};

onMounted(() => {
    if (window.google && window.google.maps) {
        initMap();
    } else {
        alert('Google Maps API wurde nicht geladen.');
    }
});

watch(() => [props.latitude, props.longitude], () => {
    if (window.google && window.google.maps) {
        initMap();
    }
});
</script>