<script setup lang="ts">
import { twMerge } from 'tailwind-merge';
import { computed, ref } from 'vue';
import { useMotion } from '@vueuse/motion';

const IconStarFilled = `<svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.049 2.152a1.5 1.5 0 011.902 0l1.621 3.436a1.5 1.5 0 001.194.887l3.778.548a1.5 1.5 0 01.832 2.578l-2.73 2.66a1.5 1.5 0 00-.435 1.334l.643 3.766a1.5 1.5 0 01-2.175 1.583l-3.38-1.777a1.5 1.5 0 00-1.396 0l-3.38 1.777a1.5 1.5 0 01-2.175-1.583l.643-3.766a1.5 1.5 0 00-.435-1.334l-2.73-2.66a1.5 1.5 0 01.832-2.578l3.778-.548a1.5 1.5 0 001.194-.887l1.621-3.436z"/></svg>`;
const IconStarOutline = `<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.152A1.5 1.5 0 0113.951 2.152l1.621 3.436a1.5 1.5 0 001.194.887l3.778.548a1.5 1.5 0 01.832 2.578l-2.73 2.66a1.5 1.5 0 00-.435 1.334l.643 3.766a1.5 1.5 0 01-2.175 1.583l-3.38-1.777a1.5 1.5 0 00-1.396 0l-3.38 1.777a1.5 1.5 0 01-2.175-1.583l.643-3.766a1.5 1.5 0 00-.435-1.334l-2.73-2.66a1.5 1.5 0 01.832-2.578l3.778-.548a1.5 1.5 0 001.194-.887l1.621-3.436z" /></svg>`;
const IconCart = `<svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`;

interface ProductCardData {
    id: number;
    name: string;
    mpn: string;
    cost: number;
    stock_qty: number;
    images?: { image: string }[];
    total_unique_views?: number;
    seo?: { slug: string };
}

interface ProductCardProps {
    product: ProductCardData;
    className?: string;
    wishlisted?: boolean; // passed in from parent
}

const props = defineProps<ProductCardProps>();
const emit = defineEmits(['addToCart', 'favourite']);

const isWishlisted = ref(props.wishlisted ?? false);

const isPopular = computed(() => (props.product.total_unique_views || 0) > 100);
const imageUrl = computed(() => props.product.images?.[0]?.image || 'https://via.placeholder.com/300?text=No+Image');
const isTapped = ref(false);

const springTransition = { type: 'spring', stiffness: 200, damping: 10, mass: 1 };
const cardRef = ref<HTMLElement | null>(null);
const innerRef = ref<HTMLElement | null>(null);

const motionCard = useMotion(cardRef, { initial: { x: 0, y: 0 }, hovered: { x: -6, y: -6 } }, { transition: springTransition });
const motionInner = useMotion(innerRef, { initial: { x: 0, y: 0 }, hovered: { x: -6, y: -6 } }, { transition: springTransition });

const baseCardClass = "group w-full rounded-lg border-2 border-copy";
const innerCardClass = "relative rounded-lg -m-0.5 border-2 border-copy bg-foreground flex flex-col justify-between overflow-hidden";
const accentColor = 'bg-primary-light';

const mergedBaseClass = computed(() => twMerge(baseCardClass, props.className));
const mergedInnerClass = computed(() => twMerge(innerCardClass, accentColor));

const truncatedName = computed(() => {
    const maxLen = 25;
    return props.product.name.length > maxLen
        ? props.product.name.substring(0, maxLen).trim() + '...'
        : props.product.name;
});

const formattedCost = computed(() => {
    const n = Number(props.product.cost);
    return isNaN(n) ? 'N/A' : `£${n.toFixed(2)}`;
});

const productLink = computed(() =>
    props.product.seo?.slug
        ? `/product/${props.product.seo.slug}`
        : `/product/${props.product.id}`
);

const handleFavourite = () => {
    isWishlisted.value = !isWishlisted.value;
    emit('favourite', props.product.id);
};

const handleTouchStart = () => {
    if (!isTapped.value) {
        isTapped.value = true;
        motionCard.apply('hovered');
        motionInner.apply('hovered');
        document.addEventListener('touchstart', handleTouchEnd, { once: true, capture: true });
    }
};

const handleTouchEnd = (event: Event) => {
    const target = event.target as HTMLElement;
    if (cardRef.value && !cardRef.value.contains(target)) {
        isTapped.value = false;
        motionCard.apply('initial');
        motionInner.apply('initial');
    } else if (isTapped.value) {
        setTimeout(() => {
            isTapped.value = false;
            motionCard.apply('initial');
            motionInner.apply('initial');
        }, 300);
    }
    document.removeEventListener('touchstart', handleTouchEnd, { capture: true });
};
</script>

<template>
    <div ref="cardRef" @mouseenter="motionCard.apply('hovered')" @mouseleave="motionCard.apply('initial')"
        @touchstart.stop="handleTouchStart" :class="[mergedBaseClass, { 'group-hovered': isTapped }]"
        style="background-color: var(--primary-content);">

        <div ref="innerRef" @mouseenter="motionInner.apply('hovered')" @mouseleave="motionInner.apply('initial')"
            :class="mergedInnerClass">

            <span v-if="isPopular"
                class="absolute top-3 left-3 z-10 inline-flex items-center rounded-full bg-error px-3 py-0.5 text-xs font-bold text-error-content shadow-lg ring-1 ring-inset ring-error-content/50">
                🔥 POPULAR
            </span>

            <a :href="productLink" class="absolute inset-0 z-[1] block">
                <span class="sr-only">View product: {{ product.name }}</span>
            </a>

            <button class="absolute top-3 right-3 z-20 p-2 rounded-full border transition-colors shadow-md" :class="isWishlisted
                ? 'bg-error text-error-content border-error hover:bg-error-dark'
                : 'bg-foreground border-border hover:bg-error-light hover:text-error-content'"
                :aria-label="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
                :title="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
                @click.stop.prevent="handleFavourite">
                <div v-if="isWishlisted" v-html="IconStarFilled" class="size-5"></div>
                <div v-else v-html="IconStarOutline" class="size-5"></div>
            </button>

            <div class="h-48 overflow-hidden bg-foreground flex items-center justify-center">
                <img :src="imageUrl" :alt="'Image of ' + product.name"
                    class="w-full h-full object-cover transition duration-500 group-hover:scale-105" />
            </div>

            <div class="p-4 border-t-2 border-copy bg-foreground">
                <div class="block">
                    <p class="text-xs font-medium uppercase tracking-wider text-copy-lighter">{{ product.mpn }}</p>
                    <p class="flex items-center text-xl font-bold text-copy transition truncate mt-1">
                        <span class="mr-2">{{ truncatedName }}</span>
                    </p>
                </div>

                <div class="mt-4 flex justify-between items-end">
                    <p class="text-3xl font-black text-primary-content">{{ formattedCost }}</p>

                    <button
                        class="z-20 p-2 border-2 border-copy text-primary-content rounded-lg transition-colors duration-300 ease-in-out hover:bg-primary-dark hover:text-foreground shadow-md"
                        aria-label="Add product to cart" @click.stop="$emit('addToCart', product.id)"
                        style="background-color: var(--primary);">
                        <div v-html="IconCart"></div>
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.group-hovered .group-hover\:scale-105 {
    transform: scale(1.05);
}

.group-hovered .group-hover\:translate-y-0 {
    transform: translateY(0);
}

.group-hovered .group-hover\:opacity-100 {
    opacity: 1;
}

.group-hovered .group-hover\:bg-sky-600 {
    background-color: var(--primary-dark);
}

.group-hovered .group-hover\:text-white {
    color: var(--foreground);
}

.group-hovered .group-hover\:text-sky-600 {
    color: var(--primary-content);
}
</style>
