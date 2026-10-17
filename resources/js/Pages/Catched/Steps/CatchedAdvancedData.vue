<template>
    <div class="space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <VInput label="Tiefe (Zentimeter)" type="number" v-model="props.modelValue.depth" :error="errors?.depth" />
            <VInput label="Wassertemperatur (°C)" type="number" v-model="props.modelValue.temperature" :error="errors?.temperature" />
            <VInput label="Luftdruck (hPa)" type="number" v-model="props.modelValue.air_pressure" :error="errors?.air_pressure"
                supportingText="Nur falls selbst gemessen – Wetter & Luftdruck ergänzt PetriLog automatisch." />
            <VInput label="Köder" type="text" v-model="props.modelValue.bait" :error="errors?.bait" />
        </div>
        <p class="text-sm text-(--app-muted)">Alle Felder sind optional. Fehlt noch was? Schreib uns an <a class="ui-link"
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