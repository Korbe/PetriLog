<template>
    <PageWrapper title="Bundesland bearbeiten" backTo="/admin/state">
        <div class="ui-card mx-auto max-w-4xl p-5 md:p-8">
            <div>
                <form @submit.prevent="submit">

                    <VFileInput v-if="canUploadImage" type="file" v-model="form.photo" accept="image/*"
                        :multiple="false" />

                    <div v-if="errors?.['photos.0']" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors['photos.0'] }}</div>

                    <ImagePreview class="mt-5" :modelValue="form.photo" :existing="props.state.media"
                        @remove="removeImage" />

                    <VInput label="Name" v-model="form.name" :error="form.errors.name" />
                    <VEditor class="my-4" label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <div class="flex items-center justify-between">
                        <VButton type="submit" :disabled="form.processing">
                            Speichern
                        </VButton>

                        <!-- <VButton type="button" variant="danger" @click="destroy">
                            Löschen
                        </VButton> -->
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
import { useForm, router } from '@inertiajs/vue3';
import VFileInput from '@/components/VFileInput.vue';
import ImagePreview from '@/components/ImagePreview.vue';
import { computed } from 'vue'
import { confirmDialog } from '@/composables/useDialog';
import VEditor from '@/components/VEditor.vue';

const props = defineProps({
    state: Object,
    errors: Object,
});

// Form initialisieren mit bestehenden Daten
const form = useForm({
    name: props.state.name,
    desc: props.state.desc,
    photo: null,
});

const canUploadImage = computed(() => {
    // Zeige FileInput nur, wenn:
    // 1. Noch kein File im Form gesetzt ist
    // 2. Keine bestehende Media vorhanden
    return !form.photo && (!props.state.media || props.state.media.length === 0)
})

function submit() {
    form.post(route('admin.state.update', props.state.id));
}

// function destroy() {
//   if (confirm(`Möchtest du das Bundesland "${props.state.name}" wirklich löschen?`)) {
//     Inertia.delete(route('admin.state.destroy', props.state.id));
//   }
// }

const removeImage = async (item) => {
    if (item.file instanceof File) {
        form.photo = null;
        return
    }

    if (item.readonly) {
        const confirmed = await confirmDialog({
            title: 'Wappen löschen?',
            message: `Das Bild von ${props.state.name} wird dauerhaft entfernt.`,
            confirmText: 'Bild löschen',
            tone: 'danger',
        })
        if (!confirmed) return

        router.delete(route('admin.state.photo.delete', item.id), {
            onSuccess: () => {
                form.media = null
            },
        })
    }
}
</script>