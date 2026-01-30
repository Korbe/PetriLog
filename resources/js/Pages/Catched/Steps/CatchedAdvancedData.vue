<template>
    <div class="space-y-5">
        <VInput label="Tiefe (Centimeter)" type="number" v-model="props.modelValue.depth" :error="errors?.depth" />
        <VInput label="Temperatur (°C)" type="number" v-model="props.modelValue.temperature" :error="errors?.temperature" />
        <VInput label="Luftdruck (hPa)" type="number" v-model="props.modelValue.air_pressure" :error="errors?.air_pressure" />
        <VInput label="Köder" type="text" v-model="props.modelValue.bait" :error="errors?.bait" />
        <p>Fehlt noch was? Schreib uns eine Mail an <a class="underline"
                href="mailto:info@petrilog.com">info@petrilog.com</a></p>
    </div>
</template>
<script setup>
import VInput from '@/components/VInput.vue';

const props = defineProps({
    modelValue: Object,
    errors: Object
})

const emit = defineEmits(['update:modelValue'])

</script>