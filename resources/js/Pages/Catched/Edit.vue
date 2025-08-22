<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import CatchedForm from './CatchedForm.vue';

defineProps({
    catched: Object
});
</script>
<template>
    <PageWrapper title="Fang bearbeiten" :backTo="route('catched.show', catched.id)">
        <CatchedForm :catched="catched" :isEdit="true" />
    </PageWrapper>
</template>

