<template>
    <div class="space-y-5">
        <VDateTimePicker v-model="props.modelValue.date" label="Datum" mandatory />

        <VSelect label="Fisch Art" id="fishname" :options="fishSpeciesAustria" placeholder="Bitte wählen..." mandatory
            v-model="props.modelValue.name" :error="errors?.name" :reduce="option => option.value" />

        <VSelect label="Gewässer" id="watername" placeholder="Bitte wählen..." v-model="props.modelValue.waters"
            :reduce="option => option.value" :options="watersAustria" :error="errors?.waters" mandatory />

        <span class="block text-sm font-medium mb-5" v-if="!showCustomWatersField"
            @click="showCustomWatersField = true">
            Dein Gewässer ist nicht dabei? Klick hier
        </span>

        <VInput v-if="showCustomWatersField" label="Gib dein Gewässer ein" v-model="props.modelValue.waters"
            :error="errors?.waters" />

        <VInput label="Länge (Centimeter)" type="number" mandatory v-model="props.modelValue.length" :error="errors?.length" />
        <VInput label="Gewicht (Gramm)" type="number" v-model="props.modelValue.weight" :error="errors?.weight" />
    </div>
</template>
<script setup>
import { ref } from 'vue';
import VDateTimePicker from '@/components/VDateTimePicker.vue';
import VInput from '@/components/VInput.vue';
import VSelect from '@/components/VSelect.vue';

const fishSpeciesAustria = [
    { label: 'Aal', value: 'Aal' },
    { label: 'Aalrutte', value: 'Aalrutte' },
    { label: 'Adriastör', value: 'Adriastör' },
    { label: 'Aitel', value: 'Aitel' },
    { label: 'Amur', value: 'Amur' },
    { label: 'Atlantischer Stör', value: 'Atlantischer Stör' },
    { label: 'Äsche', value: 'Äsche' },
    { label: 'Bachforelle', value: 'Bachforelle' },
    { label: 'Bachneunauge', value: 'Bachneunauge' },
    { label: 'Bachsaibling', value: 'Bachsaibling' },
    { label: 'Bachschmerle', value: 'Bachschmerle' },
    { label: 'Barbe', value: 'Barbe' },
    { label: 'Bitterling', value: 'Bitterling' },
    { label: 'Blaubandbärbling', value: 'Blaubandbärbling' },
    { label: 'Brachse', value: 'Brachse' },
    { label: 'Donaukaulbarsch', value: 'Donaukaulbarsch' },
    { label: 'Dreistachliger Stichling', value: 'Dreistachliger Stichling' },
    { label: 'Elritze', value: 'Elritze' },
    { label: 'Flussbarsch', value: 'Flussbarsch' },
    { label: 'Frauennerfling', value: 'Frauennerfling' },
    { label: 'Giebel', value: 'Giebel' },
    { label: 'Glattdick', value: 'Glattdick' },
    { label: 'Goldsteinbeißer', value: 'Goldsteinbeißer' },
    { label: 'Gründling', value: 'Gründling' },
    { label: 'Güster', value: 'Güster' },
    { label: 'Hasel', value: 'Hasel' },
    { label: 'Hausen', value: 'Hausen' },
    { label: 'Hecht', value: 'Hecht' },
    { label: 'Huchen', value: 'Huchen' },
    { label: 'Karausche', value: 'Karausche' },
    { label: 'Karpfen', value: 'Karpfen' },
    { label: 'Kaulbarsch', value: 'Kaulbarsch' },
    { label: 'Kesslergründling', value: 'Kesslergründling' },
    { label: 'Koppe', value: 'Koppe' },
    { label: 'Laube', value: 'Laube' },
    { label: 'Malermuschel', value: 'Malermuschel' },
    { label: 'Moderlieschen', value: 'Moderlieschen' },
    { label: 'Nackthalsgrundel', value: 'Nackthalsgrundel' },
    { label: 'Nase', value: 'Nase' },
    { label: 'Nerfling', value: 'Nerfling' },
    { label: 'Perlfisch', value: 'Perlfisch' },
    { label: 'Rapfen', value: 'Rapfen' },
    { label: 'Regenbogenforelle', value: 'Regenbogenforelle' },
    { label: 'Reinanke', value: 'Reinanke' },
    { label: 'Rotauge', value: 'Rotauge' },
    { label: 'Rotfeder', value: 'Rotfeder' },
    { label: 'Rußnase', value: 'Rußnase' },
    { label: 'Schleie', value: 'Schleie' },
    { label: 'Schlammpeitzger', value: 'Schlammpeitzger' },
    { label: 'Schneider', value: 'Schneider' },
    { label: 'Schrätzer', value: 'Schrätzer' },
    { label: 'Schwarzmundgrundel', value: 'Schwarzmundgrundel' },
    { label: 'Seeforelle', value: 'Seeforelle' },
    { label: 'Seelaube', value: 'Seelaube' },
    { label: 'Seesaibling', value: 'Seesaibling' },
    { label: 'Semling', value: 'Semling' },
    { label: 'Sichling', value: 'Sichling' },
    { label: 'Sonnenbarsch', value: 'Sonnenbarsch' },
    { label: 'Steingreßling', value: 'Steingreßling' },
    { label: 'Steinbeißer', value: 'Steinbeißer' },
    { label: 'Sterlet', value: 'Sterlet' },
    { label: 'Sternhausen', value: 'Sternhausen' },
    { label: 'Streber', value: 'Streber' },
    { label: 'Tolstolob', value: 'Tolstolob' },
    { label: 'Ukrainisches Bachneunauge', value: 'Ukrainisches Bachneunauge' },
    { label: 'Waxdick', value: 'Waxdick' },
    { label: 'Weißflossengründling', value: 'Weißflossengründling' },
    { label: 'Weißer Stör', value: 'Weißer Stör' },
    { label: 'Wels', value: 'Wels' },
    { label: 'Wolgazander', value: 'Wolgazander' },
    { label: 'Zander', value: 'Zander' },
    { label: 'Zingel', value: 'Zingel' },
    { label: 'Zobel', value: 'Zobel' },
    { label: 'Zope', value: 'Zope' },
    { label: 'Zwergwels', value: 'Zwergwels' }
];

const watersAustria = [
    { label: 'Achensee', value: 'Achensee' },
    { label: 'Ach', value: 'Ach' },
    { label: 'Ager', value: 'Ager' },
    { label: 'Aist', value: 'Aist' },
    { label: 'Alfenz', value: 'Alfenz' },
    { label: 'Alm', value: 'Alm' },
    { label: 'Altausseer See', value: 'Altausseer See' },
    { label: 'Alter Rhein', value: 'Alter Rhein' },
    { label: 'Antiesen', value: 'Antiesen' },
    { label: 'Arbesbach', value: 'Arbesbach' },
    { label: 'Aschach', value: 'Aschach' },
    { label: 'Aurach', value: 'Aurach' },
    { label: 'Attersee', value: 'Attersee' },
    { label: 'Bodensee', value: 'Bodensee' },
    { label: 'Donau', value: 'Donau' },
    { label: 'Drau', value: 'Drau' },
    { label: 'Erlaufsee', value: 'Erlaufsee' },
    { label: 'Enns', value: 'Enns' },
    { label: 'Faaker See', value: 'Faaker See' },
    { label: 'Fuschlsee', value: 'Fuschlsee' },
    { label: 'Gail', value: 'Gail' },
    { label: 'Grabensee', value: 'Grabensee' },
    { label: 'Grundlsee', value: 'Grundlsee' },
    { label: 'Gurk', value: 'Gurk' },
    { label: 'Haldensee', value: 'Haldensee' },
    { label: 'Hallstätter See', value: 'Hallstätter See' },
    { label: 'Heiterwanger See', value: 'Heiterwanger See' },
    { label: 'Hintersee', value: 'Hintersee' },
    { label: 'Hintersteiner See', value: 'Hintersteiner See' },
    { label: 'Illmitzer Zicksee', value: 'Illmitzer Zicksee' },
    { label: 'Inn', value: 'Inn' },
    { label: 'Irrsee', value: 'Irrsee' },
    { label: 'Kamp', value: 'Kamp' },
    { label: 'Keutschacher See', value: 'Keutschacher See' },
    { label: 'Klopeiner See', value: 'Klopeiner See' },
    { label: 'Lange Lacke', value: 'Lange Lacke' },
    { label: 'Längsee', value: 'Längsee' },
    { label: 'Leitha', value: 'Leitha' },
    { label: 'Lunzer See', value: 'Lunzer See' },
    { label: 'March', value: 'March' },
    { label: 'Mattsee', value: 'Mattsee' },
    { label: 'Millstätter See', value: 'Millstätter See' },
    { label: 'Mondsee', value: 'Mondsee' },
    { label: 'Mur', value: 'Mur' },
    { label: 'Neufelder See', value: 'Neufelder See' },
    { label: 'Neusiedler See', value: 'Neusiedler See' },
    { label: 'Obertrumer See', value: 'Obertrumer See' },
    { label: 'Offensee', value: 'Offensee' },
    { label: 'Ossiacher See', value: 'Ossiacher See' },
    { label: 'Plansee', value: 'Plansee' },
    { label: 'Pressegger See', value: 'Pressegger See' },
    { label: 'Raab', value: 'Raab' },
    { label: 'Salzach', value: 'Salzach' },
    { label: 'Thaya', value: 'Thaya' },
    { label: 'Traun', value: 'Traun' },
    { label: 'Traunsee', value: 'Traunsee' },
    { label: 'Vilsalpsee', value: 'Vilsalpsee' },
    { label: 'Vorderer Gosausee', value: 'Vorderer Gosausee' },
    { label: 'Walchsee', value: 'Walchsee' },
    { label: 'Wallersee', value: 'Wallersee' },
    { label: 'Weissensee', value: 'Weissensee' },
    { label: 'Wolfgangsee', value: 'Wolfgangsee' },
    { label: 'Wörthersee', value: 'Wörthersee' },
    { label: 'Ybbs', value: 'Ybbs' },
    { label: 'Zeller See', value: 'Zeller See' },
    { label: 'Zicksee', value: 'Zicksee' }
];

const props = defineProps({
    modelValue: Object,
    errors: Object
})

const emit = defineEmits(['update:modelValue'])

const showCustomWatersField = ref(false);

</script>