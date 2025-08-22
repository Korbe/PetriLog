<template>
    <p class="text-center text-md font-medium text-gray-700 mb-5">Tippe auf die Positon in der Karte</p>

    <GoogleMapPicker :initialLat="modelValue.latitude" :initialLng="modelValue.longitude"
        @locationSelected="updateLocation" />

        <p v-if="!modelValue.latitude" class="text-center text-md font-medium text-gray-700 my-5">Noch keine Position eingegeben</p>

    <VInput v-if="modelValue.address" label="Adresse" v-model="modelValue.address" :error="errors?.address" />
    <VInput v-if="modelValue.latitude" label="Latitude" v-model="modelValue.latitude" :error="errors?.latitude" />
    <VInput v-if="modelValue.longitude" label="Longitude" v-model="modelValue.longitude" :error="errors?.longitude" />
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