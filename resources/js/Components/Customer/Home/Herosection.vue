<script setup>

import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    website: {
        type: Object,
        default: () => ({}),
    },

    general: {
        type: Object,
        default: () => ({}),
    },
});

console.log('HOME WEBSITE PROPS:', props.website);
console.log('HOME GENERAL PROPS:', props.general);

/*
|--------------------------------------------------------------------------
| HERO BACKGROUND IMAGES
|--------------------------------------------------------------------------
*/

const heroImages = [
    '/images/home/pharmacy-hero.jpg',
    '/images/home/pharmacy-hero-2.jpg',
    '/images/home/pharmacy-hero-3.jpg',
    '/images/home/pharmacy-hero-4.jpg',
];

const activeSlide = ref(0);
let slideInterval = null;

/*
|--------------------------------------------------------------------------
| HERO SLIDER
|--------------------------------------------------------------------------
*/

onMounted(() => {
    slideInterval = setInterval(() => {
        activeSlide.value =
            (activeSlide.value + 1) % heroImages.length;
    }, 6000);
});

onBeforeUnmount(() => {
    if (slideInterval) {
        clearInterval(slideInterval);
    }
});

/*
|--------------------------------------------------------------------------
| WEBSITE SETTINGS
|--------------------------------------------------------------------------
*/

const heroTitle =
    props.website?.hero_title ??
    'Healthcare';

const heroSubtitle =
    props.website?.hero_subtitle ??
    'made simple.';

const businessName =
    props.website?.title ??
    'Go Pharmacy';

const businessTagline =
    props.general?.['business.tagline'] ??
    'GOOD HEALTH. MADE SIMPLE.';
/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

const searchQuery = ref('');

const sanitizeSearch = (value) => {
    return value.replace(/[^a-zA-Z0-9\s]/g, '');
};

const handleSearchInput = (event) => {
    searchQuery.value = sanitizeSearch(event.target.value);
};

const search = () => {
    const query = sanitizeSearch(searchQuery.value).trim();

    if (!query) {
        return;
    }

    window.location.href =
        `/shop?search=${encodeURIComponent(query)}`;
};

</script>

<template>
    <section
        class="relative isolate min-h-[calc(100vh-128px)] overflow-hidden bg-slate-950 text-white"
    >
        <!-- =========================================================
             CINEMATIC BACKGROUND
        ========================================================== -->

        <div class="absolute inset-0 -z-20 overflow-hidden">
            <div
                v-for="(image, index) in heroImages"
                :key="image"
                class="absolute inset-0 h-full w-full overflow-hidden transition-opacity duration-[2500ms] ease-in-out"
                :class="
                    activeSlide === index
                        ? 'opacity-100'
                        : 'opacity-0'
                "
            >
                <img
                    :src="image"
                    alt=""
                    aria-hidden="true"
                    class="h-full w-full object-cover transition-transform duration-[8000ms] ease-out"
                    :class="
                        activeSlide === index
                            ? 'scale-105'
                            : 'scale-100'
                    "
                />
            </div>
        </div>

        <!-- =========================================================
             CINEMATIC OVERLAY
        ========================================================== -->

        <div
            class="absolute inset-0 -z-10 bg-slate-950/55"
        ></div>

        <!-- Left readability gradient -->

        <div
            class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/85 via-slate-950/55 to-slate-950/20"
        ></div>

        <!-- Bottom cinematic gradient -->

        <div
            class="absolute inset-x-0 bottom-0 -z-10 h-64 bg-gradient-to-t from-slate-950/80 to-transparent"
        ></div>

        <!-- Subtle green atmosphere -->

        <div
            class="absolute -left-40 top-1/3 -z-10 h-[32rem] w-[32rem] rounded-full bg-green-500/10 blur-[120px]"
        ></div>

        <!-- =========================================================
             HERO CONTENT
        ========================================================== -->

        <div
            class="mx-auto flex min-h-[calc(100vh-128px)] max-w-7xl items-center px-4 py-20 sm:px-6 lg:px-8"
        >
            <div class="max-w-3xl">

                <!-- =================================================
                     EYEBROW
                ================================================== -->

                <div
                    class="mb-7 flex items-center gap-4"
                >
                    <span
                        class="text-xs font-semibold uppercase tracking-[0.28em] text-green-300"
                    >
                        {{ businessTagline }}
                    </span>
                </div>

                <!-- =================================================
                     MAIN HEADING
                ================================================== -->

                <h1
                    class="max-w-3xl text-5xl font-semibold leading-[1.02] tracking-[-0.045em] text-white sm:text-6xl lg:text-7xl xl:text-[5.8rem]"
                >
                    {{ heroTitle }}

                    <span
                        class="block text-green-400"
                    >
                        {{ heroSubtitle }}
                    </span>
                </h1>

                <!-- =================================================
                     DESCRIPTION
                ================================================== -->

                <p
                    class="mt-7 max-w-2xl text-base font-normal leading-7 text-slate-200 sm:text-lg sm:leading-8"
                >
                    Shop trusted medicines, healthcare essentials and
                    everyday wellness products from

                    <span class="font-semibold text-white">
                        {{ businessName }}
                    </span>

                    all in one simple, convenient experience.
                </p>

                <!-- =================================================
                     SEARCH
                ================================================== -->

                <form
                    class="mt-9 max-w-2xl"
                    @submit.prevent="search"
                >
                    <label
                        for="hero-search"
                        class="sr-only"
                    >
                        Search medicines and healthcare products
                    </label>

                    <div
                        class="flex items-center rounded-2xl border border-white/20 bg-white/95 p-1.5 shadow-2xl backdrop-blur-xl transition duration-300 focus-within:border-green-400 focus-within:ring-4 focus-within:ring-green-400/20"
                    >
                        <!-- Search icon -->

                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center"
                        >
                            <svg
                                class="h-5 w-5 text-slate-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                                />
                            </svg>
                        </div>

                        <!-- Search input -->

                        <input
                            id="hero-search"
                            v-model="searchQuery"
                            type="search"
                            inputmode="text"
                            autocomplete="off"
                            maxlength="100"
                            placeholder="Search medicines, vitamins, wellness..."
                            class="min-w-0 flex-1 border-0 bg-transparent px-2 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:ring-0"
                            @input="handleSearchInput"
                        />

                        <!-- Search button -->

                        <button
                            type="submit"
                            class="hidden rounded-xl bg-green-600 px-7 py-3 text-sm font-semibold text-white transition duration-300 hover:bg-green-700 sm:block"
                        >
                            Search
                        </button>
                    </div>
                </form>

                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div
                    class="mt-7 flex flex-col gap-3 sm:flex-row"
                >
                    <Link
                        href="/shop"
                        class="inline-flex items-center justify-center rounded-xl bg-green-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-green-950/20 transition duration-300 hover:-translate-y-0.5 hover:bg-green-700 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-green-400/30"
                    >
                        Shop Now

                        <svg
                            class="ml-2 h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M5 12h14m-6-6 6 6-6 6"
                            />
                        </svg>
                    </Link>

                    <Link
                        :href="route('prescriptions.index')"
                        class="inline-flex items-center justify-center rounded-xl border border-white/30 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white backdrop-blur-md transition duration-300 hover:-translate-y-0.5 hover:border-white/50 hover:bg-white/15 focus:outline-none focus:ring-4 focus:ring-white/20"
                    >
                        Upload Prescription

                        <svg
                            class="ml-2 h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M12 16V4m0 0-4 4m4-4 4 4M5 16v3h14v-3"
                            />
                        </svg>
                    </Link>
                </div>

                <!-- =================================================
                     TRUST INDICATORS
                ================================================== -->

                <div
                    class="mt-10 flex flex-wrap gap-x-8 gap-y-4"
                >
                    <div
                        class="flex items-center gap-2.5"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-green-500/20 text-green-300"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>
                        </span>

                        <span
                            class="text-sm font-medium text-slate-200"
                        >
                            Quality healthcare products
                        </span>
                    </div>

                    <div
                        class="flex items-center gap-2.5"
                    >
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-green-500/20 text-green-300"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 3v18m9-9H3"
                                />
                            </svg>
                        </span>

                        <span
                            class="text-sm font-medium text-slate-200"
                        >
                            Pharmacy-led care
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================================================
             SLIDE INDICATORS
        ========================================================== -->

        <div
            class="absolute bottom-8 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2 rounded-full border border-white/10 bg-black/20 px-3 py-2 backdrop-blur-md"
        >
            <button
                v-for="(_, index) in heroImages"
                :key="`slide-${index}`"
                type="button"
                :aria-label="`Show slide ${index + 1}`"
                class="h-1.5 rounded-full transition-all duration-700 ease-out"
                :class="
                    activeSlide === index
                        ? 'w-9 bg-green-400'
                        : 'w-2 bg-white/40 hover:bg-white/70'
                "
                @click="activeSlide = index"
            ></button>
        </div>

        <!-- =========================================================
             SCROLL INDICATOR
        ========================================================== -->

        <div
            class="absolute bottom-8 right-8 hidden items-center gap-3 text-white/50 lg:flex"
        >
            <span
                class="text-[10px] font-medium uppercase tracking-[0.25em]"
            >
                Explore
            </span>

            <span
                class="h-10 w-px bg-white/20"
            ></span>
        </div>
    </section>
</template>