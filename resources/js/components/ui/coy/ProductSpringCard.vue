<script setup lang="ts">
import { twMerge } from 'tailwind-merge';
import { computed, ref } from 'vue';
import { useMotion } from '@vueuse/motion';

const IconStar = `<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.152A1.5 1.5 0 0113.951 2.152l1.621 3.436a1.5 1.5 0 001.194.887l3.778.548a1.5 1.5 0 01.832 2.578l-2.73 2.66a1.5 1.5 0 00-.435 1.334l.643 3.766a1.5 1.5 0 01-2.175 1.583l-3.38-1.777a1.5 1.5 0 00-1.396 0l-3.38 1.777a1.5 1.5 0 01-2.175-1.583l.643-3.766a1.5 1.5 0 00-.435-1.334l-2.73-2.66a1.5 1.5 0 01.832-2.578l3.778-.548a1.5 1.5 0 001.194-.887l1.621-3.436z" /></svg>`;
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
}

const props = defineProps<ProductCardProps>();

const isPopular = computed(() => (props.product.total_unique_views || 0) > 100);
const imageUrl = computed(() => props.product.images?.[0]?.image || 'https://via.placeholder.com/300?text=No+Image');
const isTapped = ref(false);

const springTransition = {
  type: 'spring',
  stiffness: 200,
  damping: 10,
  mass: 1,
};

const cardRef = ref<HTMLElement | null>(null);
const innerRef = ref<HTMLElement | null>(null);

const motionCard = useMotion(cardRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -6, y: -6 },
}, {
  transition: springTransition,
});

const motionInner = useMotion(innerRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -6, y: -6 },
}, {
  transition: springTransition,
});

// Use 'copy' for the bold border and 'foreground' for the inner card's main color
const baseCardClass = "group w-full rounded-lg border-2 border-copy";
const innerCardClass = "relative rounded-lg -m-0.5 border-2 border-copy bg-foreground flex flex-col justify-between overflow-hidden";
// Use a primary light shade for the accent background color
const accentColor = 'bg-primary-light';

const mergedBaseClass = computed(() => twMerge(baseCardClass, props.className));
const mergedInnerClass = computed(() => twMerge(innerCardClass, accentColor));

const truncatedName = computed(() => {
    const maxLen = 25;
    const name = props.product.name;
    if (name.length > maxLen) {
        return name.substring(0, maxLen).trim() + '...';
    }
    return name;
});

const formattedCost = computed(() => {
  const numericCost = Number(props.product.cost);
  if (isNaN(numericCost)) {
    return 'N/A';
  }
  return `£${numericCost.toFixed(2)}`;
});

const handleTouchStart = () => {
    if (!isTapped.value) {
        // First tap: toggle the state and trigger the spring animation
        isTapped.value = true;
        motionCard.apply('hovered');
        motionInner.apply('hovered');

        // Add a document listener to reset state if user taps elsewhere
        document.addEventListener('touchstart', handleTouchEnd, { once: true, capture: true });
    }
    // If the card is already tapped (hovered), the next tap should act as a click/navigation,
    // which the anchor tag will handle, but we ensure the state resets immediately after.
};

const handleTouchEnd = (event: Event) => {
    // Check if the tap target is *inside* the card. If so, let the anchor/button click fire.
    // If the tap is *outside* the card, reset the 'hovered' state.
    const target = event.target as HTMLElement;
    if (cardRef.value && !cardRef.value.contains(target)) {
        isTapped.value = false;
        motionCard.apply('initial');
        motionInner.apply('initial');
    } else if (isTapped.value) {
        // If the tap was inside and the card was active, reset state after a short delay
        // to let the button/link action fire cleanly.
        setTimeout(() => {
            isTapped.value = false;
            motionCard.apply('initial');
            motionInner.apply('initial');
        }, 300);
    }
    document.removeEventListener('touchstart', handleTouchEnd, { capture: true });
};

const productLink = computed(() => {
    // Check if an SEO slug exists and use it
    if (props.product.seo && props.product.seo.slug) {
        return `/product/${props.product.seo.slug}`;
    }
    // Fallback to using the product ID
    return `/product/${props.product.id}`;
});
</script>

<template>
  <div
    ref="cardRef"
    @mouseenter="motionCard.apply('hovered')"
    @mouseleave="motionCard.apply('initial')"
    @touchstart.stop="handleTouchStart"
    :class="[mergedBaseClass, { 'group-hovered': isTapped }]"
    style="background-color: var(--primary-content);"  >
    <div
      ref="innerRef"
      @mouseenter="motionInner.apply('hovered')"
      @mouseleave="motionInner.apply('initial')"
      :class="mergedInnerClass"
    >
      <span v-if="isPopular" class="absolute top-3 left-3 z-10 inline-flex items-center rounded-full bg-error px-3 py-0.5 text-xs font-bold text-error-content shadow-lg ring-1 ring-inset ring-error-content/50">
          🔥 POPULAR
      </span>

      <button
        class="absolute top-3 right-3 z-10 p-2 rounded-full bg-foreground border border-border transition-colors hover:bg-error-light hover:text-error-content shadow-md"
        aria-label="Add to favourites"
        @click.stop.prevent="$emit('favourite', product.id)"
      >
        <div v-html="IconStar" class="size-5"></div>
      </button>

      <div class="h-48 overflow-hidden bg-foreground flex items-center justify-center p-4">
        <img
          :src="imageUrl"
          :alt="'Image of ' + product.name"
          class="w-full h-full object-contain transition duration-500 group-hover:scale-105"
        />
      </div>

      <div class="p-4 border-t-2 border-copy bg-foreground">
        <a :href="productLink" class="block">
          <p class="text-xs font-medium uppercase tracking-wider text-copy-lighter">{{ product.mpn }}</p>
          <p class="flex items-center text-xl font-bold text-copy transition truncate mt-1">
            <span class="mr-2">{{ truncatedName }}</span>
          </p>
        </a>

        <div class="mt-4 flex justify-between items-end">
            <p class="text-3xl font-black text-primary-content">
                {{ formattedCost }}
            </p>

            <button
                class="z-20 p-2 border-2 border-copy text-primary-content rounded-lg transition-colors duration-300 ease-in-out hover:bg-primary-dark hover:text-foreground shadow-md"
                aria-label="Add product to cart"
                @click.stop="$emit('addToCart', product.id)"
                style="background-color: var(--primary);"
            >
                <div v-html="IconCart"></div>
            </button>
        </div>
      </div>

      <svg
          v-motion
          :initial="{ rotate: 0 }"
          :animate="{ rotate: 360 }"
          :transition="{ duration: 25000, repeat: Infinity, type: 'tween', ease: 'linear' }"
          style="bottom: 0; right: 0; transform: translate(50%, 50%) scale(0.6);"
          width="100"
          height="100"
          class="pointer-events-none absolute z-0 rounded-full text-copy-lighter opacity-20"
      >
          <path id="circlePath-{{ product.id }}" d="M50,50 m-50,0 a50,50 0 1,0 100,0 a50,50 0 1,0 -100,0" fill="none" />
          <text>
              <textPath
                  :href="'#circlePath-' + product.id"
                  fill="currentColor"
                  class="text-xs font-black uppercase"
              >
                  BUY NOW • IN STOCK • BUY NOW • IN STOCK •
              </textPath>
          </text>
      </svg>
    </div>
  </div>
</template>

<style scoped>
/* NOTE: The existing group-hovered classes below still use hardcoded hex or default Tailwind colors.
   Since you've defined custom colors, you MUST replace these with the correct CSS variable names
   or the Tailwind utility classes if they are being correctly processed by the compiler.

   For safety and consistency with your CSS variable setup, I'll update these to use the new variables.
*/

.group-hovered .group-hover\:translate-x-1 {
    transform: translateX(0.25rem);
}
.group-hovered .group-hover\:scale-105 {
    transform: scale(1.05);
}
.group-hovered .group-hover\:translate-y-0 {
    transform: translateY(0);
}
.group-hovered .group-hover\:opacity-100 {
    opacity: 1;
}
/* Replaced generic sky-600 with primary-dark/primary-content equivalents */
.group-hovered .group-hover\:bg-sky-600 {
    background-color: var(--primary-dark);
}
.group-hovered .group-hover\:text-white {
    color: var(--foreground); /* Use foreground for white/light text */
}
.group-hovered .group-hover\:text-sky-600 {
    color: var(--primary-content); /* Use primary-content for the main accent text color */
}
</style>
