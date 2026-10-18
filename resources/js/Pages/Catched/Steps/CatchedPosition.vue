<template>
    <div class="space-y-4">
        <p class="text-sm text-(--app-muted)">Tippe auf die Stelle in der Karte, an der du gefangen hast.</p>

        <MapPicker @locationSelected="updateLocation" />

        <p v-if="!modelValue.latitude" class="ui-panel px-4 py-3 text-center text-sm text-(--app-muted)">Noch keine Position gewählt.</p>

        <VInput v-if="modelValue.address" label="Adresse" v-model="modelValue.address" :error="errors?.address" />
        <div v-if="modelValue.latitude" class="grid gap-4 sm:grid-cols-2">
            <VInput label="Breitengrad" v-model="modelValue.latitude" :error="errors?.latitude" />
            <VInput v-if="modelValue.longitude" label="Längengrad" v-model="modelValue.longitude" :error="errors?.longitude" />
        </div>
    </div>
</template>

<script setup>
import MapPicker from '@/components/MapPicker.vue'
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