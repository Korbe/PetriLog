<script setup>
import Layout from '@/Layouts/Layout.vue';
import CatchedForm from './CatchedForm.vue';

const props = defineProps({
    errors: Object, // Fehler vom Backend
})
</script>

<template>
    <Layout title="Fang eintragen" :backTo="route('catched.index')">
        <CatchedForm :errors=errors :isEdit="false" />
    </Layout>
</template>
