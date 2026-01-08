<template>
    <PageWrapper title="Fisch hinzufügen" backTo="/admin/fish">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">

            <div class="p-6">
                <form @submit.prevent="submit">

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <VTextarea class="mt-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />
                    <VTextarea class="my-4" label="Tipps" v-model="form.hint" :error="form.errors.hint" />

                    <VButton type="submit" :disabled="form.processing">
                        Speichern
                    </VButton>
                </form>
            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import VButton from '@/components/VButton.vue';
import VInput from '@/components/VInput.vue';
import VTextarea from '@/components/VTextarea.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';

import { useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    desc: '',
});

function submit() {
    form.post(route('admin.fish.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
}
</script>