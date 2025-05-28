<script setup>
import Layout from '@/Layouts/Layout.vue';
</script>

<template>
    <Layout :title="'Petri Heil ' + $page.props.auth.user.name">
        <template v-slot:actions>
        </template>


        <div class="py-12 space-y-5">
        </div>
    </Layout>
</template>
