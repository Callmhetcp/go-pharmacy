<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    advertisements: {
        type: Array,
        default: () => [],
    },
});

const currentIndex = ref(0);
let autoplayTimer = null;

const activeAdvertisements = computed(() => {
    const now = new Date();

    return props.advertisements.filter((advertisement) => {
        if (!advertisement?.is_active) {
            return false;
        }

        if (
            advertisement.starts_at &&
            new Date(advertisement.starts_at) > now
        ) {
            return false;
        }

        if (
            advertisement.ends_at &&
            new Date(advertisement.ends_at) < now
        ) {
            return false;
        }

        return true;
    });
});

const hasAdvertisements = computed(
    () => activeAdvertisements.value.length > 0
);

const hasMultipleAdvertisements = computed(
    () => activeAdvertisements.value.length > 1
);

const currentAdvertisement = computed(
    () => activeAdvertisements.value[currentIndex.value] ?? null
);

const normalizeCurrentIndex = () => {
    const total = activeAdvertisements.value.length;

    if (total === 0) {
        currentIndex.value = 0;
        return;
    }

    if (currentIndex.value >= total) {
        currentIndex.value = 0;
    }
};

const nextSlide = () => {
    if (!hasMultipleAdvertisements.value) {
        return;
    }

    currentIndex.value =
        (currentIndex.value + 1) %
        activeAdvertisements.value.length;
};

const previousSlide = () => {
    if (!hasMultipleAdvertisements.value) {
        return;
    }

    currentIndex.value =
        (currentIndex.value -
            1 +
            activeAdvertisements.value.length) %
        activeAdvertisements.value.length;
};

const goToSlide = (index) => {
    if (
        index < 0 ||
        index >= activeAdvertisements.value.length
    ) {
        return;
    }

    currentIndex.value = index;
};

const startAutoplay = () => {
    stopAutoplay();

    if (!hasMultipleAdvertisements.value) {
        return;
    }

    autoplayTimer = window.setInterval(() => {
        nextSlide();
    }, 5000);
};

const stopAutoplay = () => {
    if (autoplayTimer !== null) {
        window.clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
};

watch(
    activeAdvertisements,
    () => {
        normalizeCurrentIndex();
        startAutoplay();
    },
    { immediate: true }
);

onMounted(() => {
    startAutoplay();
});

onBeforeUnmount(() => {
    stopAutoplay();
});
</script>

<template>
    <section
        v-if="hasAdvertisements"
        class="bg-white py-6 transition-colors duration-300 dark:bg-slate-950"
        aria-label="Go Pharmacy advertisements"
    >
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="relative overflow-hidden rounded-2xl bg-slate-100 shadow-sm dark:bg-slate-900"
                @mouseenter="stopAutoplay"
                @mouseleave="startAutoplay"
            >
                <!-- Slides -->
                <div
                    v-for="(advertisement, index) in activeAdvertisements"
                    :key="advertisement.id ?? index"
                    class="transition-all duration-700 ease-in-out"
                    :class="
                        index === currentIndex
                            ? 'relative opacity-100'
                            : 'pointer-events-none absolute inset-0 opacity-0'
                    "
                >
                    <!-- Advertisement Image -->
                    <img
                        v-if="advertisement.image_url"
                        :src="advertisement.image_url"
                        :alt="
                            advertisement.title ||
                            'Go Pharmacy advertisement'
                        "
                        class="h-[260px] w-full object-cover sm:h-[340px] lg:h-[420px]"
                    />

                    <!-- Fallback Background -->
                    <div
                        v-else
                        class="h-[260px] w-full bg-gradient-to-br from-green-600 to-green-800 sm:h-[340px] lg:h-[420px]"
                        aria-hidden="true"
                    ></div>

                    <!-- Dark Overlay -->
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-transparent"
                        aria-hidden="true"
                    ></div>

                    <!-- Advertisement Content -->
                    <div
                        class="absolute inset-0 flex items-center px-6 py-8 sm:px-10 lg:px-14"
                    >
                        <div class="max-w-xl text-white">
                            <!-- Badge -->
                            <p
                                v-if="advertisement.badge"
                                class="mb-3 inline-flex rounded-full bg-green-600/90 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white"
                            >
                                {{ advertisement.badge }}
                            </p>

                            <!-- Title -->
                            <h2
                                v-if="advertisement.title"
                                class="text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl"
                            >
                                {{ advertisement.title }}
                            </h2>

                            <!-- Description -->
                            <p
                                v-if="advertisement.description"
                                class="mt-3 text-sm leading-6 text-white/90 sm:text-base"
                            >
                                {{ advertisement.description }}
                            </p>

                            <!-- Action -->
                            <a
                                v-if="advertisement.product_url"
                                :href="advertisement.product_url"
                                class="mt-5 inline-flex items-center rounded-xl bg-green-600 px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-2 focus:ring-offset-transparent"
                            >
                                {{
                                    advertisement.button_text ||
                                    'Shop Now'
                                }}

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
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Previous Button -->
                <button
                    v-if="hasMultipleAdvertisements"
                    type="button"
                    aria-label="Previous advertisement"
                    class="absolute left-4 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm transition hover:bg-black/60 focus:outline-none focus:ring-2 focus:ring-white/70"
                    @click="previousSlide"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>

                <!-- Next Button -->
                <button
                    v-if="hasMultipleAdvertisements"
                    type="button"
                    aria-label="Next advertisement"
                    class="absolute right-4 top-1/2 z-20 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm transition hover:bg-black/60 focus:outline-none focus:ring-2 focus:ring-white/70"
                    @click="nextSlide"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>

                <!-- Dot Indicators -->
                <div
                    v-if="hasMultipleAdvertisements"
                    class="absolute bottom-5 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2"
                >
                    <button
                        v-for="(advertisement, index) in activeAdvertisements"
                        :key="`dot-${advertisement.id ?? index}`"
                        type="button"
                        :aria-label="`Go to advertisement ${index + 1}`"
                        :aria-current="
                            index === currentIndex
                                ? 'true'
                                : undefined
                        "
                        class="h-2 rounded-full transition-all duration-300"
                        :class="
                            index === currentIndex
                                ? 'w-7 bg-white'
                                : 'w-2 bg-white/50 hover:bg-white/80'
                        "
                        @click="goToSlide(index)"
                    ></button>
                </div>

                <!-- Slide Counter -->
                <div
                    v-if="hasMultipleAdvertisements"
                    class="absolute bottom-5 right-5 z-20 rounded-full bg-black/40 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm"
                    aria-live="polite"
                >
                    {{ currentIndex + 1 }} /
                    {{ activeAdvertisements.length }}
                </div>
            </div>
        </div>
    </section>
</template>