<template>
    <div class="space-y-5">
        <VDateTimePicker v-model="props.modelValue.date" label="Datum" mandatory />
        <div class="grid gap-4 sm:grid-cols-2">
            <VInput label="Länge (cm)" type="number" mandatory v-model="props.modelValue.length" :error="errors?.length" />
            <VInput label="Gewicht (g)" type="number" v-model="props.modelValue.weight" :error="errors?.weight" />
        </div>
        <ReleaseToggle v-model="props.modelValue.released" label="Verbleib" block
            hint="Für deine Fangmeldung ans Revier – kannst du auch später nachtragen." />
    </div>
</template>

<script setup>
import VDateTimePicker from '@/components/VDateTimePicker.vue';
import VInput from '@/components/VInput.vue'
import ReleaseToggle from '@/components/ReleaseToggle.vue'

const props = defineProps({
    modelValue: Object,
    errors: Object,
})
const emit = defineEmits(['update:modelValue'])
</script>