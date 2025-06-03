<template>
    <Layout title="Alle Fänge">
        <template v-slot:actions>
            <DropdownFilter :options="filters" @filtersChanged="handleFiltersChanged" />
            <Datepicker align="right" v-model="dateRange" />
            <VButton :href="route('catched.create')">Eintragen</VButton>
        </template>

        <div v-if="Object.keys(groupedCatcheds).length === 0" class="text-center bg-white rounded-lg shadow-lg p-5 flex flex-col my-2">
            <p class="pb-5">Noch keine Fänge eingetragen</p>
            <VButton :href="route('catched.create')">Jetzt eintragen</VButton>
                        </div>

        <Disclosure as="div" v-for="(items, date, index) in groupedCatcheds" :key="date" class="py-2"
                v-slot="{ open }" :defaultOpen="index === 0">
                <dt>
                    <DisclosureButton
                        class="bg-white rounded-lg shadow-lg p-5 flex w-full items-start justify-between text-left text-brand-headline sm:text-3xl dark:text-brand-headline-dark">
                        <span class="cursor-pointer text-base/7 font-semibold">{{ date }} ({{ items.length }})</span>
                        <span class="ml-6 flex h-7 items-center">
                            <PlusIcon v-if="!open" class="cursor-pointer size-6" aria-hidden="true" />
                            <MinusIcon v-else class="cursor-pointer size-6" aria-hidden="true" />
                        </span>
                    </DisclosureButton>
                </dt>
                <DisclosurePanel as="dd" class="mt-2 pl-2 pr-2">
                    <div v-for="catched in items" :key="catched.id">
                            <Link class="bg-white rounded-lg shadow-lg p-5 flex justify-between my-2" :href="route('catched.show', catched.id)"><span><b>{{ catched.name }}</b> - {{ catched.waters }}</span> <ChevronRightIcon class="h-6" /></Link>
                    </div>
                </DisclosurePanel>
            </Disclosure>
    </Layout>
</template>

<script setup lang="ts">
import { Disclosure, DisclosureButton, DisclosurePanel } from '@headlessui/vue'
import { MinusIcon, PlusIcon } from '@heroicons/vue/24/outline'
import { ref, watch } from "vue";
import { router } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import VButton from "@/components/VButton.vue";
import Datepicker from "@/components/VDateRangePicker.vue";
import DropdownFilter from "@/components/DropdownFilter.vue";
import { ChevronRightIcon } from '@heroicons/vue/24/solid';

interface CatchedData {
    id: number;
    user_id: number;
    name: string;
    length: number;
    weight: number;
    depth: number;
    temperature: number;
    waters: string;
    date: string;
    latitude: number;
    longitude: number;
    address: string;
    remark: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    groupedCatcheds: Record<string, CatchedData[]>;
    dateRange: {
        startDate: string;
        endDate: string;
    };
}

const props = defineProps<Props>();

let isUpdating = false;

const dateRange = ref({
    startDate: new Date(props.dateRange?.startDate || new Date().setDate(new Date().getDate() - 7)),
    endDate: new Date(props.dateRange?.endDate || new Date())
});

const filters = ref([
    { label: 'Beschreibung', value: 'onlyWithDescription', selected: false },
])

watch(dateRange, (newRange) => {
    handleDateRangeUpdate(newRange);
}, { deep: true });



const getFilterParams = () => {
    return filters.value
        .filter(filter => filter.selected)
        .map(filter => ({ [filter.value]: 1 }))
        .reduce((acc, curr) => ({ ...acc, ...curr }), {});
};


const handleDateRangeUpdate = (newRange) => {
    // Check if both dates are present and valid
    if (!newRange?.startDate || !newRange?.endDate) {
        dateRange.value = newRange; // Update local state but don't trigger search
        return;
    }

    // Validate dates are actual Date objects
    if (!(newRange.startDate instanceof Date) || !(newRange.endDate instanceof Date))
        return;

    // Check if dates are valid (not NaN)
    if (isNaN(newRange.startDate.getTime()) || isNaN(newRange.endDate.getTime()))
        return;

    dateRange.value = newRange;

    search();
};

const handleFiltersChanged = (newFilters) => {
    filters.value = newFilters;

    search();
};

const search = () => {
    if (isUpdating) return;

    isUpdating = true;

    const filterParams = getFilterParams();
    console.log(filterParams);

    const request = {
        startDate: dateRange.value.startDate.toLocaleString(),
        endDate: dateRange.value.endDate.toLocaleString(),
        ...filterParams
    };

    console.log(request);

    router.get(
        route('catched.index'), request,
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                setTimeout(() => {
                    isUpdating = false;
                }, 0);
            }
        }
    );
};

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return day + "." + month + "." + year;
}
</script>