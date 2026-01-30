<template>
    <div class="space-y-5">
        <!-- Fisch -->
        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Fischart <span class="text-red-500">*</span>
        </label>
        <VMultiselect ref="ms" @open="onOpen" v-model="selectedFish" :options="fish" label="name" track-by="id"
            placeholder="Fisch auswählen" />
        <div v-if="errors?.fish_id" class="text-xs mt-1 text-red-500">{{ errors.fish_id }}</div>

        <!-- Lake & River -->
        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            See auswählen <span class="text-red-500">*</span>
        </label>
        <VMultiselect v-model="selectedLake" :options="lakes" label="name" track-by="id" placeholder="See auswählen" />
        <div v-if="errors?.lake_id" class="text-xs mt-1 text-red-500">{{ errors.lake_id }}</div>

        <label class="block text-md md:text-sm font-medium text-gray-700 dark:text-gray-400 mb-1">
            Fluss auswählen <span class="text-red-500">*</span>
        </label>
        <VMultiselect v-model="selectedRiver" :options="rivers" label="name" track-by="id"
            placeholder="Fluss auswählen" />
        <div v-if="errors?.river_id" class="text-xs mt-1 text-red-500">{{ errors.river_id }}</div>

        <p>Dein Gewässer ist nicht dabei? Schreib uns eine Mail an <a class="underline"
                href="mailto:info@petrilog.com">info@petrilog.com</a></p>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import VDateTimePicker from '@/components/VDateTimePicker.vue'
import VInput from '@/components/VInput.vue'
import VMultiselect from '@/components/VMultiselect.vue'

const props = defineProps({
    modelValue: Object,
    errors: Object,
    fish: Array,
    lakes: Array,
    rivers: Array,
})

const emit = defineEmits(['update:modelValue'])

// Fish Multiselect
const selectedFish = computed({
    get() { return props.fish.find(f => f.id === props.modelValue.fish_id) ?? null },
    set(value) { emit('update:modelValue', { ...props.modelValue, fish_id: value?.id ?? null }) }
})

// Lake & River Multiselect (nur eines darf gewählt werden)
const selectedLake = computed({
    get() { return props.lakes.find(l => l.id === props.modelValue.lake_id) ?? null },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, lake_id: value?.id ?? null, river_id: null })
    }
})

const selectedRiver = computed({
    get() { return props.rivers.find(r => r.id === props.modelValue.river_id) ?? null },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, river_id: value?.id ?? null, lake_id: null })
    }
})
</script>