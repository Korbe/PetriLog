<template>
    <PageWrapper title="Fisch hinzufügen" backTo="/admin/fish">
        <div class="w-full bg-white flex flex-col justify-center dark:bg-gray-800 shadow-xs rounded-lg p-5">

            <div class="p-6">
                <form @submit.prevent="submit">

                    <VFileInput v-if="!form.photo" type="file" v-model="form.photo" accept="image/*" :multiple="false"
                        class="block w-full focus:ring-brand-primary focus:border-brand-primary" />

                    <div v-if="errors?.['photos.0']" class="text-xs mt-1 text-red-500">{{ errors['photos.0'] }}</div>

                    <ImagePreview class="mt-5" :modelValue="form.photo" @remove="removeImage" />

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    
                    <VEditor label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <VButton type="submit" :disabled="form.processing">
                        Speichern
                    </VButton>
                </form>
            </div>
        </div>
    </PageWrapper>
</template>
<script setup>
import ImagePreview from '@/components/ImagePreview.vue';
import VButton from '@/components/VButton.vue';
import VEditor from '@/components/VEditor.vue';
import VFileInput from '@/components/VFileInput.vue';
import VInput from '@/components/VInput.vue';
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue';

import { useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    desc: '',
    photo: null,
});

function submit() {
    form.post(route('admin.fish.store'));
}

const removeImage = (item) => {
    form.photo = null;
}
</script>