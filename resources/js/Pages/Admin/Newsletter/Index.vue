<template>
    <PageWrapper title="Newsletter" backTo="/admin">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">

            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Betreff" v-model="form.subject" :error="form.errors.subject" />
                    <VEditor label="Inhalt" v-model="form.content" :error="form.errors.content" />

                    <div class="flex gap-3 mt-5">
                        <VButton type="submit" :disabled="form.processing">
                            Newsletter senden
                        </VButton>

                        <VButton type="button" variant="secondary" :disabled="form.processing" @click="sendTest">
                            An mich senden
                        </VButton>
                    </div>
                </form>
            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';
import { useForm } from '@inertiajs/vue3'
import VEditor from '@/components/VEditor.vue';

const props = defineProps({
    states: Array,
});

const form = useForm({
    subject: '',
    content: '',
});

function submit() {
    form.post(route('admin.newsletter.send'));
}

function sendTest() {
    form.post(route('admin.newsletter.test'))
}
</script>