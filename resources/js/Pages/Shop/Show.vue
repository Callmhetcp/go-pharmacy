<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },

    relatedProducts: {
        type: Array,
        default: () => [],
    },
});

const quantity = ref(1);
const addingToCart = ref(false);

/*
|--------------------------------------------------------------------------
| Inventory
|--------------------------------------------------------------------------
*/

const inventory = computed(() => props.product.inventory);

const availableQuantity = computed(() => {
    if (!inventory.value) {
        return 0;
    }

    return Math.max(
        0,
        Number(inventory.value.quantity ?? 0) -
            Number(inventory.value.reserved_quantity ?? 0)
    );
});

const isInStock = computed(() => availableQuantity.value > 0);

const isLowStock = computed(() => {
    if (!inventory.value) {
        return false;
    }

    return (
        availableQuantity.value <=
        Number(inventory.value.minimum_stock ?? 0)
    );
});

/*
|--------------------------------------------------------------------------
| Product
|--------------------------------------------------------------------------
*/

const formattedPrice = computed(() => {
    return Number(props.product.price ?? 0).toLocaleString('en-NG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
});

/*
|--------------------------------------------------------------------------
| Quantity
|--------------------------------------------------------------------------
*/

const decreaseQuantity = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

const increaseQuantity = () => {
    if (quantity.value < availableQuantity.value) {
        quantity.value++;
    }
};

/*
|--------------------------------------------------------------------------
| Add To Cart
|--------------------------------------------------------------------------
*/

const addToCart = () => {
    if (
        !props.product.is_active ||
        !isInStock.value ||
        addingToCart.value
    ) {
        return;
    }

    addingToCart.value = true;

    router.post(
        route('cart.store'),
        {
            product_id: props.product.id,
            quantity: quantity.value,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                quantity.value = 1;
            },

            onFinish: () => {
                addingToCart.value = false;
            },
        }
    );
};

/*
|--------------------------------------------------------------------------
| Reviews
|--------------------------------------------------------------------------
*/

const reviews = ref([]);
const reviewsLoading = ref(false);
const reviewsError = ref('');
const reviewsCurrentPage = ref(1);
const reviewsLastPage = ref(1);
const reviewsTotal = ref(0);

const reviewsCount = computed(() => reviewsTotal.value);

const averageRating = computed(() => {
    if (!reviews.value.length) {
        return 0;
    }

    const total = reviews.value.reduce(
        (sum, review) => sum + Number(review.rating ?? 0),
        0
    );

    return total / reviews.value.length;
});

const formattedAverageRating = computed(() => {
    return averageRating.value.toFixed(1);
});

const loadReviews = async (page = 1) => {
    reviewsLoading.value = true;
    reviewsError.value = '';

    try {
        const response = await fetch(
            `/api/v1/products/${props.product.id}/reviews?page=${page}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            }
        );

        if (!response.ok) {
            throw new Error('Unable to load reviews.');
        }

        const data = await response.json();

        if (page === 1) {
            reviews.value = data.data ?? [];
        } else {
            reviews.value.push(...(data.data ?? []));
        }

        reviewsCurrentPage.value =
            data.meta?.current_page ?? page;

        reviewsLastPage.value =
            data.meta?.last_page ?? page;

        reviewsTotal.value =
            data.meta?.total ?? reviews.value.length;
    } catch (error) {
        reviewsError.value =
            error.message || 'Unable to load reviews.';
    } finally {
        reviewsLoading.value = false;
    }
};

const loadMoreReviews = () => {
    if (
        reviewsLoading.value ||
        reviewsCurrentPage.value >= reviewsLastPage.value
    ) {
        return;
    }

    loadReviews(reviewsCurrentPage.value + 1);
};

const formatReviewDate = (createdAt) => {
    if (!createdAt) {
        return '';
    }

    return new Date(createdAt).toLocaleDateString('en-NG', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

/*
|--------------------------------------------------------------------------
| Submit Review
|--------------------------------------------------------------------------
*/

const reviewRating = ref(0);
const reviewComment = ref('');
const reviewSubmitting = ref(false);
const reviewError = ref('');
const reviewSuccess = ref('');

const setReviewRating = (rating) => {
    reviewRating.value = rating;
    reviewError.value = '';
};

const submitReview = async () => {
    reviewError.value = '';
    reviewSuccess.value = '';

    if (!reviewRating.value) {
        reviewError.value = 'Please select a rating.';
        return;
    }

    if (!reviewComment.value.trim()) {
        reviewError.value = 'Please write a comment.';
        return;
    }

    reviewSubmitting.value = true;

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const response = await fetch(
            `/api/v1/products/${props.product.id}/reviews`,
            {
                method: 'POST',

                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken ?? '',
                },

                credentials: 'same-origin',

                body: JSON.stringify({
                    rating: reviewRating.value,
                    comment: reviewComment.value.trim(),
                }),
            }
        );

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 422) {
                const validationErrors = data.errors
                    ? Object.values(data.errors).flat()
                    : [];

                reviewError.value =
                    validationErrors[0] ||
                    data.message ||
                    'Please check your review details.';

                return;
            }

            if (response.status === 401) {
                reviewError.value =
                    'Please log in to submit a review.';

                return;
            }

            reviewError.value =
                data.message ||
                'Unable to submit your review. Please try again.';

            return;
        }

        reviewRating.value = 0;
        reviewComment.value = '';

       reviewSuccess.value =
            'Thank you! Your review has been submitted successfully.';
    } catch (error) {
        reviewError.value =
            'Unable to submit your review. Please try again.';
    } finally {
        reviewSubmitting.value = false;
    }
};

onMounted(() => {
    loadReviews();
});
</script>

<template>
    <CustomerLayout>
        <Head :title="product.name" />

    <main class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <!-- =====================================================
             BREADCRUMB
        ====================================================== -->

        <div
            class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8"
            >
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <Link
                        href="/"
                        class="text-slate-500 transition hover:text-green-600"
                    >
                        Home
                    </Link>

                    <span class="text-slate-300">/</span>

                    <Link
                        href="/shop"
                        class="text-slate-500 transition hover:text-green-600"
                    >
                        Shop
                    </Link>

                    <span class="text-slate-300">/</span>

                    <span
                        class="font-medium text-slate-900 dark:text-white"
                    >
                        {{ product.name }}
                    </span>
                </div>
            </div>
        </div>

        <!-- =====================================================
             PRODUCT
        ====================================================== -->

        <section
            class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-16"
        >
            <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
                <!-- Product Image -->

                <div>
                    <div
                        class="relative aspect-square overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <img
                            v-if="product.image"
                            :src="`/storage/${product.image}`"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                        />

                        <div
                            v-else
                            class="flex h-full items-center justify-center bg-slate-100 dark:bg-slate-800"
                        >
                            <div class="text-center">
                                <svg
                                    class="mx-auto h-16 w-16 text-slate-300 dark:text-slate-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    />
                                </svg>

                                <p
                                    class="mt-3 text-sm text-slate-400"
                                >
                                    Product image unavailable
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="product.requires_prescription"
                            class="absolute left-5 top-5 rounded-full bg-amber-100 px-4 py-2 text-xs font-bold uppercase tracking-wide text-amber-800"
                        >
                            Prescription Required
                        </div>
                    </div>
                </div>

                <!-- Product Information -->

                <div class="flex flex-col justify-center">
                    <Link
                        v-if="product.category"
                        :href="`/shop?category=${product.category.slug}`"
                        class="inline-flex w-fit text-sm font-bold uppercase tracking-[0.18em] text-green-600"
                    >
                        {{ product.category.name }}
                    </Link>

                    <h1
                        class="mt-4 text-4xl font-black tracking-tight text-slate-950 sm:text-5xl dark:text-white"
                    >
                        {{ product.name }}
                    </h1>

                    <p
                        v-if="product.brand"
                        class="mt-4 text-lg text-slate-500 dark:text-slate-400"
                    >
                        {{ product.brand }}
                    </p>

                    <div
                        v-if="product.generic_name"
                        class="mt-6"
                    >
                        <p
                            class="text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Generic Name
                        </p>

                        <p
                            class="mt-1 text-slate-700 dark:text-slate-300"
                        >
                            {{ product.generic_name }}
                        </p>
                    </div>

                    <div class="mt-8">
                        <span
                            class="text-4xl font-black text-slate-950 dark:text-white"
                        >
                            ₦{{ formattedPrice }}
                        </span>
                    </div>

                    <!-- Product Details -->

                    <div
                        v-if="product.strength || product.dosage_form"
                        class="mt-8 grid grid-cols-2 gap-4"
                    >
                        <div
                            v-if="product.strength"
                            class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                Strength
                            </p>

                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ product.strength }}
                            </p>
                        </div>

                        <div
                            v-if="product.dosage_form"
                            class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                Dosage Form
                            </p>

                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ product.dosage_form }}
                            </p>
                        </div>
                    </div>

                    <!-- Description -->

                    <div
                        v-if="product.description"
                        class="mt-8 border-t border-slate-200 pt-8 dark:border-slate-800"
                    >
                        <h2
                            class="font-bold text-slate-900 dark:text-white"
                        >
                            About this product
                        </h2>

                        <p
                            class="mt-3 leading-7 text-slate-600 dark:text-slate-400"
                        >
                            {{ product.description }}
                        </p>
                    </div>

                    <!-- Stock Status -->

                    <div class="mt-8">
                        <div
                            v-if="isInStock && product.is_active"
                            class="flex flex-wrap items-center gap-2"
                        >
                            <span
                                class="h-2.5 w-2.5 rounded-full bg-green-500"
                            ></span>

                            <span
                                class="text-sm font-semibold text-green-700 dark:text-green-400"
                            >
                                In stock
                            </span>

                            <span
                                v-if="isLowStock"
                                class="ml-2 text-sm text-amber-600 dark:text-amber-400"
                            >
                                Limited availability
                            </span>
                        </div>

                        <div
                            v-else-if="product.is_active && !isInStock"
                            class="flex items-center gap-2 text-red-500"
                        >
                            <span
                                class="h-3 w-3 rounded-full bg-red-500"
                            ></span>

                            <span class="font-semibold">
                                Out of stock
                            </span>
                        </div>

                        <div
                            v-else
                            class="flex items-center gap-2 text-red-500"
                        >
                            <span
                                class="h-3 w-3 rounded-full bg-red-500"
                            ></span>

                            <span class="font-semibold">
                                Currently unavailable
                            </span>
                        </div>
                    </div>

                    <!-- Purchase -->

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <div
                            v-if="isInStock && product.is_active"
                            class="flex h-14 shrink-0 items-center rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900"
                        >
                            <button
                                type="button"
                                class="flex h-full w-12 items-center justify-center text-xl font-bold text-slate-500 transition hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                                @click="decreaseQuantity"
                                :disabled="
                                    addingToCart ||
                                    quantity <= 1
                                "
                            >
                                −
                            </button>

                            <span
                                class="w-10 text-center font-bold text-slate-900 dark:text-white"
                            >
                                {{ quantity }}
                            </span>

                            <button
                                type="button"
                                class="flex h-full w-12 items-center justify-center text-xl font-bold text-slate-500 transition hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                                @click="increaseQuantity"
                                :disabled="
                                    addingToCart ||
                                    quantity >= availableQuantity
                                "
                            >
                                +
                            </button>
                        </div>

                        <button
                            type="button"
                            :disabled="
                                !product.is_active ||
                                !isInStock ||
                                addingToCart
                            "
                            @click="addToCart"
                            class="w-full rounded-xl px-6 py-4 text-base font-bold transition-all duration-200 sm:flex-1"
                            :class="
                                product.is_active &&
                                isInStock &&
                                !addingToCart
                                    ? 'bg-green-600 text-white hover:bg-green-700 active:scale-[0.98]'
                                    : 'cursor-not-allowed bg-slate-300 text-white'
                            "
                        >
                            <span v-if="addingToCart">
                                Adding to Cart...
                            </span>

                            <span
                                v-else-if="!product.is_active"
                            >
                                Currently Unavailable
                            </span>

                            <span
                                v-else-if="!isInStock"
                            >
                                Out of Stock
                            </span>

                            <span v-else>
                                Add to Cart
                            </span>
                        </button>
                    </div>

                    <!-- Prescription Notice -->

                    <div
                        v-if="product.requires_prescription"
                        class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20"
                    >
                        <div class="flex gap-3">
                            <svg
                                class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"
                                />
                            </svg>

                            <div>
                                <p
                                    class="font-bold text-amber-900 dark:text-amber-300"
                                >
                                    Prescription required
                                </p>

                                <p
                                    class="mt-1 text-sm leading-6 text-amber-800 dark:text-amber-400"
                                >
                                    A valid prescription may be required
                                    before this product can be dispensed.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             CUSTOMER REVIEWS
        ====================================================== -->

        <section
            class="border-t border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"
            >
                <div
                    class="grid gap-10 lg:grid-cols-[280px_1fr]"
                >
                    <!-- Rating Summary -->

                    <div>
                        <p
                            class="text-sm font-bold uppercase tracking-[0.18em] text-green-600"
                        >
                            Customer Reviews
                        </p>

                        <div class="mt-4">
                            <div
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="text-4xl font-black text-slate-950 dark:text-white"
                                >
                                    {{ formattedAverageRating }}
                                </span>

                                <div>
                                    <div
                                        class="flex items-center gap-1"
                                        aria-label="Average rating"
                                    >
                                        <span
                                            v-for="star in 5"
                                            :key="star"
                                            class="text-xl"
                                            :class="
                                                star <=
                                                Math.round(
                                                    averageRating
                                                )
                                                    ? 'text-amber-400'
                                                    : 'text-slate-300 dark:text-slate-600'
                                            "
                                        >
                                            ★
                                        </span>
                                    </div>

                                    <p
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        {{ reviewsCount }}
                                        {{
                                            reviewsCount === 1
                                                ? 'review'
                                                : 'reviews'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Review Content -->

                    <div>
                        <!-- Write Review -->

                        <div
                            class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div>
                                <h2
                                    class="text-xl font-bold text-slate-900 dark:text-white"
                                >
                                    Write a review
                                </h2>

                                <p
                                    class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    Share your experience with this product.
                                </p>
                            </div>

                            <!-- Rating -->

                            <div class="mt-6">
                                <p
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Your rating
                                </p>

                                <div
                                    class="mt-2 flex items-center gap-1"
                                    role="radiogroup"
                                    aria-label="Select your rating"
                                >
                                    <button
                                        v-for="star in 5"
                                        :key="star"
                                        type="button"
                                        class="text-3xl leading-none transition-transform hover:scale-110 focus:outline-none"
                                        :class="
                                            star <= reviewRating
                                                ? 'text-amber-400'
                                                : 'text-slate-300 dark:text-slate-600'
                                        "
                                        :aria-label="`${star} star${star === 1 ? '' : 's'}`"
                                        :aria-checked="
                                            reviewRating === star
                                        "
                                        role="radio"
                                        @click="setReviewRating(star)"
                                    >
                                        ★
                                    </button>

                                    <span
                                        v-if="reviewRating"
                                        class="ml-2 text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        {{ reviewRating }}/5
                                    </span>
                                </div>
                            </div>

                            <!-- Comment -->

                            <div class="mt-5">
                                <label
                                    for="review-comment"
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Your review
                                </label>

                                <textarea
                                    id="review-comment"
                                    v-model="reviewComment"
                                    rows="5"
                                    maxlength="5000"
                                    placeholder="Tell other customers about your experience..."
                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                                    :disabled="reviewSubmitting"
                                ></textarea>

                                <div
                                    class="mt-1 text-right text-xs text-slate-400"
                                >
                                    {{ reviewComment.length }}/5000
                                </div>
                            </div>

                            <!-- Error -->

                            <div
                                v-if="reviewError"
                                class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400"
                            >
                                {{ reviewError }}
                            </div>

                            <!-- Success -->

                            <div
                                v-if="reviewSuccess"
                                class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/50 dark:bg-green-950/20 dark:text-green-400"
                            >
                                {{ reviewSuccess }}
                            </div>

                            <!-- Submit -->

                            <div class="mt-5">
                                <button
                                    type="button"
                                    class="rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="reviewSubmitting"
                                    @click="submitReview"
                                >
                                    {{
                                        reviewSubmitting
                                            ? 'Submitting...'
                                            : 'Submit review'
                                    }}
                                </button>
                            </div>

                            <p
                                class="mt-3 text-xs leading-5 text-slate-400"
                            >
                                Reviews are checked and approved before
                                they appear publicly.
                            </p>
                        </div>

                        <!-- Loading -->

                        <div
                            v-if="reviewsLoading && !reviews.length"
                            class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400"
                        >
                            Loading reviews...
                        </div>

                        <!-- Error -->

                        <div
                            v-else-if="reviewsError && !reviews.length"
                            class="rounded-2xl border border-red-200 bg-red-50 p-6 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/20 dark:text-red-400"
                        >
                            {{ reviewsError }}
                        </div>

                        <!-- Empty -->

                        <div
                            v-else-if="!reviews.length"
                            class="rounded-2xl border border-slate-200 bg-white p-8 text-center dark:border-slate-800 dark:bg-slate-900"
                        >
                            <p
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                No reviews yet
                            </p>

                            <p
                                class="mt-2 text-sm text-slate-500 dark:text-slate-400"
                            >
                                Be the first customer to review this
                                product.
                            </p>
                        </div>

                        <!-- Review List -->

                        <div
                            v-else
                            class="space-y-4"
                        >
                            <article
                                v-for="review in reviews"
                                :key="review.id"
                                class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900"
                            >
                                <div
                                    class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                >
                                    <div>
                                        <p
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                review.customer?.name ||
                                                'Customer'
                                            }}
                                        </p>

                                        <div
                                            class="mt-1 flex items-center gap-1"
                                            aria-label="Review rating"
                                        >
                                            <span
                                                v-for="star in 5"
                                                :key="star"
                                                class="text-lg"
                                                :class="
                                                    star <=
                                                    Number(review.rating)
                                                        ? 'text-amber-400'
                                                        : 'text-slate-300 dark:text-slate-600'
                                                "
                                            >
                                                ★
                                            </span>
                                        </div>
                                    </div>

                                    <time
                                        :datetime="review.created_at"
                                        class="text-sm text-slate-400"
                                    >
                                        {{
                                            formatReviewDate(
                                                review.created_at
                                            )
                                        }}
                                    </time>
                                </div>

                                <p
                                    v-if="review.comment"
                                    class="mt-4 leading-7 text-slate-600 dark:text-slate-300"
                                >
                                    {{ review.comment }}
                                </p>
                            </article>

                            <!-- Load More -->

                            <div
                                v-if="
                                    reviewsCurrentPage <
                                    reviewsLastPage
                                "
                                class="pt-2 text-center"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-green-500 hover:text-green-600 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                    :disabled="reviewsLoading"
                                    @click="loadMoreReviews"
                                >
                                    {{
                                        reviewsLoading
                                            ? 'Loading...'
                                            : 'Load more reviews'
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =====================================================
             RELATED PRODUCTS
        ====================================================== -->

        <section
            v-if="relatedProducts.length"
            class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900"
        >
            <div
                class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"
            >
                <div class="mb-8">
                    <p
                        class="text-sm font-bold uppercase tracking-[0.18em] text-green-600"
                    >
                        You may also like
                    </p>

                    <h2
                        class="mt-2 text-3xl font-black text-slate-950 dark:text-white"
                    >
                        More from this category
                    </h2>
                </div>

                <div
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Link
                        v-for="item in relatedProducts"
                        :key="item.id"
                        :href="`/shop/${item.slug}`"
                        class="group overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-950"
                    >
                        <!-- Image -->

                        <div
                            class="aspect-square bg-slate-100 dark:bg-slate-800"
                        >
                            <img
                                v-if="item.image"
                                :src="`/storage/${item.image}`"
                                :alt="item.name"
                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                            />

                            <div
                                v-else
                                class="flex h-full items-center justify-center text-sm text-slate-400"
                            >
                                No image
                            </div>
                        </div>

                        <!-- Details -->

                        <div class="p-5">
                            <p
                                class="text-xs font-semibold uppercase tracking-wide text-green-600"
                            >
                                {{ item.category?.name }}
                            </p>

                            <h3
                                class="mt-2 font-bold text-slate-900 dark:text-white"
                            >
                                {{ item.name }}
                            </h3>

                            <p
                                class="mt-3 font-black text-slate-950 dark:text-white"
                            >
                                ₦{{
                                    Number(
                                        item.price ?? 0
                                    ).toLocaleString('en-NG')
                                }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </main>
</CustomerLayout>

</template>
