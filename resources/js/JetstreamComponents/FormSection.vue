<script setup>
import { computed, useSlots } from 'vue';

defineEmits(['submitted']);

const hasActions = computed(() => !! useSlots().actions);
</script>

<template>
    <div class="ui-card overflow-hidden">
        <form @submit.prevent="$emit('submitted')">
            <div class="p-5 sm:p-7">
                <slot name="header" />

                <div class="grid grid-cols-6 gap-5">
                    <slot name="form" />
                </div>
            </div>

            <div v-if="hasActions"
                class="flex items-center justify-end gap-3 border-t border-(--app-border) bg-(--app-surface-2) px-5 py-4 sm:px-7">
                <slot name="actions" />
            </div>
        </form>
    </div>
</template>
