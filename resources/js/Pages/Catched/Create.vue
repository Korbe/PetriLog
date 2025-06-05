<script setup>
import CatchedForm from './CatchedForm.vue';
import PageWrapper from '@/Layouts/PageWrapper.vue';

const props = defineProps({
    errors: Object,
})
</script>

<template>
    <PageWrapper title="Fang eintragen" :backTo="route('catched.index')">
        <CatchedForm :errors=errors :isEdit="false" />
    </PageWrapper>
</template>
