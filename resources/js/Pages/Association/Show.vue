<template>
    <PageWrapper :title="association.name" :backTo="`/states/${state_id}`">

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6">

                <h1 class="text-center font-bold text-4xl mb-2">Verein {{ association.name }}</h1>

             <a v-if="association.link" :href="association.link" target="_blank"
                                class="mt-5 flex items-center gap-2 p-2 text-blue-500 hover:text-blue-700 rounded">
                                {{ shortenLink(association.link) }}<ArrowTopRightOnSquareIcon class="w-5 h-5" />
                            </a>

                <p class="pt-5" v-html="association.desc"></p>

            </div>
        </div>

    </PageWrapper>
</template>
<script setup>
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/solid';
import { onMounted } from 'vue';

defineProps({
    state_id: String,
    association: Object
});

onMounted(() => {
    sessionStorage.setItem('lastOrigin', window.location.href);
});

const shortenLink = (link) => {
  if (!link) return '';
  let short = link.replace(/^https?:\/\//, ''); // Entferne http:// oder https://
  short = short.replace(/\/$/, ''); // Entferne / am Ende
  return short;
};

</script>