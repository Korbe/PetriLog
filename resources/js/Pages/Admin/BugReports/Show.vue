<template>
    <PageWrapper title="Fehlerbericht" backTo="/admin/bugreports">
        <div class="space-y-5">

            <div class="flex space-x-5 w-full">

                <div class="bg-white rounded-lg dark:bg-gray-800 flex flex-wrap lg:flex-nowrap w-full gap-5">

                    <div class="p-6">
                        <h1 class="text-2xl font-bold mb-4">Bug Report #{{ report.id }}</h1>
                        <div class="mb-2"><strong>Title:</strong> {{ report.title }}</div>
                        <div class="mb-2"><strong>Category:</strong> {{ report.category }}</div>
                        <div class="mb-2"><strong>User:</strong> {{ report.user }}</div>
                        <div class="mb-2"><strong>Created At:</strong> {{ report.created_at }}</div>
                        <div class="mb-4"><strong>URL:</strong> <a :href="report.url" target="_blank">{{ report.url
                                }}</a></div>
                        <div class="mb-2"><strong>Description:</strong>
                            <p>{{ report.description }}</p>
                        </div>
                        <div class="mb-2"><strong>Steps:</strong>
                            <p>{{ report.steps }}</p>
                        </div>
                        <div class="space-x-2">
                            <VButton href="/admin/bugreports" class="mt-5">
                                Back to list
                            </VButton>
                            <VButton @click="confirmDelete()" variant="danger" class="mt-5">
                                Löschen
                               </VButton>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { Inertia } from '@inertiajs/inertia';

const props = defineProps({
    report: Object,
});

function confirmDelete() {
    if (confirm('Willst du diesen Bug-Report wirklich löschen?')) {
        Inertia.delete(`/admin/bugreports/${props.report.id}`);
    }
}
</script>