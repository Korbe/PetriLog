<script setup>
import Layout from '@/Layouts/Layout.vue';
import CatchedForm from './CatchedForm.vue';

defineProps({
    catched: Object
});
</script>
<template>
    <Layout title="Fang bearbeiten" :backTo="route('catched.show', catched.id)">
        <CatchedForm :catched="catched" :isEdit="true" />
    </Layout>
</template>

