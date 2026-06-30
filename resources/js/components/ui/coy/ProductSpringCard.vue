<script setup lang="ts">
import { computed, ref } from 'vue';
import { useMotion } from '@vueuse/motion';

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

const props = defineProps<{
    product: ProductCardData;
    className?: string;
    wishlisted?: boolean;
}>();

const emit = defineEmits(['addToCart', 'favourite']);

const isWishlisted = ref(props.wishlisted ?? false);
const isPopular = computed(() => (props.product.total_unique_views || 0) > 100);
const isLowStock = computed(() =>
    props.product.stock_qty > 0 && props.product.stock_qty <= 5
);
const imageUrl = computed(() => props.product.images?.[0]?.image || '/images/placeholder.jpg');
const isTapped = ref(false);

// Spring animation — gentler values to feel soft not mechanical
const springTransition = { type: 'spring', stiffness: 180, damping: 18, mass: 1 };
const cardRef = ref<HTMLElement | null>(null);

const motionCard = useMotion(cardRef, {
    initial: { y: 0, scale: 1 },
    hovered: { y: -5, scale: 1.01 },
}, { transition: springTransition });

const truncatedName = computed(() => {
    const max = 30;
    return props.product.name.length > max
        ? props.product.name.substring(0, max).trim() + '…'
        : props.product.name;
});

const fmt = computed(() => {
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
        document.addEventListener('touchstart', handleTouchEnd, { once: true, capture: true });
    }
};

const handleTouchEnd = (event: Event) => {
    const target = event.target as HTMLElement;
    if (cardRef.value && !cardRef.value.contains(target)) {
        isTapped.value = false;
        motionCard.apply('initial');
    } else if (isTapped.value) {
        setTimeout(() => {
            isTapped.value = false;
            motionCard.apply('initial');
        }, 300);
    }
    document.removeEventListener('touchstart', handleTouchEnd, { capture: true });
};
</script>

<template>
    <div ref="cardRef" class="psc" @mouseenter="motionCard.apply('hovered')" @mouseleave="motionCard.apply('initial')"
        @touchstart.stop="handleTouchStart">

        <!-- Popular / low stock badge -->
        <span v-if="isLowStock" class="psc-badge psc-badge--low">Only {{ product.stock_qty }} left</span>
        <span v-else-if="isPopular" class="psc-badge">Popular</span>

        <!-- Invisible full-card link -->
        <a :href="productLink" class="psc-link" :aria-label="`View ${product.name}`">
            <span class="sr-only">View product: {{ product.name }}</span>
        </a>

        <!-- Wishlist button -->
        <button class="psc-wish" :class="{ 'psc-wish--active': isWishlisted }"
            :aria-label="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
            @click.stop.prevent="handleFavourite">
            <!-- Filled heart -->
            <svg v-if="isWishlisted" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                fill="currentColor">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
            <!-- Outline heart -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
            </svg>
        </button>

        <!-- Product image -->
        <div class="psc-img-wrap">
            <img :src="imageUrl" :alt="product.name" class="psc-img" />
            <div v-if="product.stock_qty <= 0" class="psc-oos">
                <span>Out of Stock</span>
            </div>
        </div>

        <!-- Card body -->
        <div class="psc-body">
            <p class="psc-name">{{ truncatedName }}</p>

            <div class="psc-footer">
                <span class="psc-price">{{ fmt }}</span>
                <button class="psc-cart" :disabled="product.stock_qty <= 0" aria-label="Add to cart"
                    @click.stop="$emit('addToCart', product.id)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                </button>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* ── Card ── */
.psc {
    width: 100%;
    border-radius: 20px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.4);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    transition: box-shadow 0.3s ease;
}

/* Deepen shadow on hover (spring handles lift) */
.psc:hover {
    box-shadow: 0 8px 32px rgba(201, 164, 164, 0.45);
}

/* Petal watermarks */
.psc::before {
    content: '✿';
    position: absolute;
    bottom: -5px;
    right: 7px;
    font-size: 3rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

.psc::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 9px;
    font-size: 0.8rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

/* ── Popular / low stock badge ── */
.psc-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 10;
    font-family: 'Nunito', sans-serif;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    background: #8c4a50;
    color: #fff;
    border-radius: 999px;
    padding: 0.18rem 0.6rem;
    box-shadow: 0 1px 6px rgba(140, 74, 80, 0.3);
}

.psc-badge--low {
    background: #a05a10;
    box-shadow: 0 1px 6px rgba(160, 90, 16, 0.3);
}

/* ── Invisible full-card link ── */
.psc-link {
    position: absolute;
    inset: 0;
    z-index: 1;
    display: block;
}

/* ── Wishlist button ── */
.psc-wish {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 20;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #eedcda;
    background: rgba(255, 250, 250, 0.92);
    color: #c9a4a4;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(4px);
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.psc-wish:hover {
    background: #faeaea;
    color: #8c4a50;
    border-color: #c9a4a4;
}

.psc-wish--active {
    background: #faeaea;
    color: #8c4a50;
    border-color: #c9a4a4;
}

/* ── Image ── */
.psc-img-wrap {
    position: relative;
    height: 190px;
    overflow: hidden;
    background: #fdf4f3;
    border-bottom: 1px solid #f0dcd8;
    flex-shrink: 0;
}

.psc-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.psc:hover .psc-img {
    transform: scale(1.05);
}

/* Out of stock overlay */
.psc-oos {
    position: absolute;
    inset: 0;
    background: rgba(253, 244, 243, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
}

.psc-oos span {
    font-family: 'Nunito', sans-serif;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #8c4a50;
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    padding: 0.22rem 0.75rem;
}

/* ── Body ── */
.psc-body {
    padding: 0.9rem 1rem 1rem;
    background: #fffafa;
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    flex: 1;
}

.psc-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.05rem;
    font-weight: 500;
    color: #2d1a1a;
    line-height: 1.3;
    margin-bottom: 0.5rem;
}

.psc-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
    padding-top: 0.6rem;
    border-top: 1px solid #f0dcd8;
}

.psc-price {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.35rem;
    font-weight: 500;
    color: #8c4a50;
    letter-spacing: -0.01em;
}

/* ── Cart button ── */
.psc-cart {
    z-index: 20;
    position: relative;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
    color: #8c4a50;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s, transform 0.2s, box-shadow 0.2s;
}

.psc-cart:hover:not(:disabled) {
    background: linear-gradient(135deg, #c47078, #a85058);
    border-color: #a85058;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(168, 80, 88, 0.25);
}

.psc-cart:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
