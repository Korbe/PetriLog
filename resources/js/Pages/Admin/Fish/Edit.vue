<template>
    <PageWrapper title="Fisch bearbeiten" backTo="/admin/fish">
        <div class="ui-card mx-auto max-w-4xl p-5 md:p-8">
            <div>
                <form @submit.prevent="submit">

                    <VFileInput v-if="canUploadImage" type="file" v-model="form.photo" accept="image/*"
                        :multiple="false" />

                    <div v-if="errors?.['photos.0']" class="text-xs mt-1.5 font-medium text-(--app-danger)">{{ errors['photos.0'] }}</div>

                    <ImagePreview class="mt-5" :modelValue="form.photo" :existing="props.fish.media"
                        @remove="removeImage" />

                    <VInput class="mt-5" label="Name" v-model="form.name" :error="form.errors.name" />

                    <VEditor label="Beschreibung" v-model="form.desc" :error="form.errors.desc" />

                    <div class="flex items-center justify-between mt-6">
                        <VButton type="submit" :disabled="form.processing">
                            Speichern
                        </VButton>

                        <VButton type="button" variant="danger" @click="destroy">
                            Löschen
                        </VButton>
                    </div>

                </form>
            </div>
        </div>
    </PageWrapper>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import VButton from '@/components/VButton.vue'
import VInput from '@/components/VInput.vue'
import PageWrapper from '@/Layouts/Dashboard/PageWrapper.vue'
import VFileInput from '@/components/VFileInput.vue'
import ImagePreview from '@/components/ImagePreview.vue'
import { computed } from 'vue'
import { confirmDialog } from '@/composables/useDialog'
import VEditor from '@/components/VEditor.vue'

const props = defineProps({
    fish: Object,
    errors: Object,
})

const form = useForm({
    name: props.fish.name,
    desc: props.fish.desc,
    photo: null,
})

const canUploadImage = computed(() => {
    // Zeige FileInput nur, wenn:
    // 1. Noch kein File im Form gesetzt ist
    // 2. Keine bestehende Media vorhanden
    return !form.photo && (!props.fish.media || props.fish.media.length === 0)
})

const submit = () => {
    form.post(route('admin.fish.update', props.fish.id))
}

const destroy = async () => {
    const confirmed = await confirmDialog({
        title: 'Fischart löschen?',
        // fish_id ist per cascadeOnDelete verknüpft – die Fänge verschwinden mit
        message: `„${props.fish.name}“ wird dauerhaft gelöscht.\n\nAchtung: Alle Fänge mit dieser Fischart werden ebenfalls gelöscht – auch die aller anderen User.`,
        confirmText: 'Fischart & Fänge löschen',
        tone: 'danger',
    })
    if (confirmed) {
        Inertia.delete(route('admin.fish.destroy', props.fish.id))
    }
}

const removeImage = async (item) => {
    if (item.file instanceof File) {
        form.photo = null;
        return
    }

    if (item.readonly) {
        const confirmed = await confirmDialog({
            title: 'Bild löschen?',
            message: `Das Bild von „${props.fish.name}“ wird dauerhaft entfernt.`,
            confirmText: 'Bild löschen',
            tone: 'danger',
        })
        if (!confirmed) return

        router.delete(route('admin.fish.photo.delete', item.id), {
            onSuccess: () => {
                form.media = null
            },
        })
    }
}
</script>