<script setup>
defineProps({
    message: String,
});
</script>

<template>
    <div v-show="message">
        <p class="text-xs font-medium text-(--app-danger)">
            {{ message }}
        </p>
    </div>
</template>
