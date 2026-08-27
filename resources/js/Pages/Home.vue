<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

import AdvertisementBanner from '@/Components/Customer/Home/AdvertisementBanner.vue';
import Herosection from '@/Components/Customer/Home/Herosection.vue';
import Categorysection from '@/Components/Customer/Home/Categorysection.vue';
import Featuredproducts from '@/Components/Customer/Home/Featuredproducts.vue';
import TrustSection from '@/Components/Customer/Home/TrustSection.vue';

defineProps({
    advertisements: {
        type: Array,
        default: () => [],
    },

    categories: {
        type: Array,
        default: () => [],
    },

    featuredProducts: {
        type: Array,
        default: () => [],
    },

    settings: {
        type: Object,
        default: () => ({
            general: {},
            website: {},
        }),
    },
});
</script>

<template>
    <CustomerLayout>
        <main
            class="min-h-screen bg-white text-slate-900 transition-colors duration-300 dark:bg-slate-950 dark:text-white"
        >
            <AdvertisementBanner
                :advertisements="advertisements"
            />

            <Herosection
                :general="settings.general"
                :website="settings.website"
            />

            <Categorysection
                :categories="categories"
            />

            <Featuredproducts
                :products="featuredProducts"
            />

            <TrustSection />
        </main>
    </CustomerLayout>
</template>