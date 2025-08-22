<template>
    <p class="text-lg my-5">Tippe auf die Positon in der Karte.</p>

    <GoogleMapPicker :initialLat="modelValue.latitude" :initialLng="modelValue.longitude"
        @locationSelected="updateLocation" />

    <VInput label="Adresse" v-model="modelValue.address" :error="errors?.address" />
    <VInput label="Latitude" v-model="modelValue.latitude" :error="errors?.latitude" />
    <VInput label="Longitude" v-model="modelValue.longitude" :error="errors?.longitude" />
</template>

<script setup>
import GoogleMapPicker from '@/components/GoogleMapPicker.vue'
import VInput from '@/components/VInput.vue'

const props = defineProps({
    modelValue: Object,
    errors: Object
})
const emit = defineEmits(['update:modelValue'])

const updateLocation = ({ lat, lng, address }) => {
    emit('update:modelValue', {
        ...props.modelValue,
        latitude: lat,
        longitude: lng,
        address: address
    })
}
</script>