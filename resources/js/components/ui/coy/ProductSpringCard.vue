<script setup lang="ts">
import { twMerge } from 'tailwind-merge';
import { computed, ref } from 'vue';
import { useMotion } from '@vueuse/motion';

// --- Icon SVGs (No external package needed) ---
// Star icon for Favourites
const IconStar = `<svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.152A1.5 1.5 0 0113.951 2.152l1.621 3.436a1.5 1.5 0 001.194.887l3.778.548a1.5 1.5 0 01.832 2.578l-2.73 2.66a1.5 1.5 0 00-.435 1.334l.643 3.766a1.5 1.5 0 01-2.175 1.583l-3.38-1.777a1.5 1.5 0 00-1.396 0l-3.38 1.777a1.5 1.5 0 01-2.175-1.583l.643-3.766a1.5 1.5 0 00-.435-1.334l-2.73-2.66a1.5 1.5 0 01.832-2.578l3.778-.548a1.5 1.5 0 001.194-.887l1.621-3.436z" /></svg>`;
// Arrow Right icon for the Title
const IconArrowRight = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>`;


// --- TypeScript Interface Definition ---
interface ProductCardData {
  id: number;
  name: string;
  mpn: string;
  cost: number;
  stock_qty: number;
  images?: { image: string }[];
  total_unique_views?: number;
}

interface ProductCardProps {
  product: ProductCardData;
  className?: string; // For container customization
}

const props = defineProps<ProductCardProps>();

// --- Derived/Computed Properties ---
const isPopular = computed(() => (props.product.total_unique_views || 0) > 100);
const imageUrl = computed(() => props.product.images?.[0]?.image || 'https://via.placeholder.com/300?text=No+Image');

// --- Animation Setup using @vueuse/motion ---
const springTransition = {
  type: 'spring',
  stiffness: 200,
  damping: 10,
  mass: 1,
};

// Target elements for motion
const cardRef = ref<HTMLElement | null>(null);
const innerRef = ref<HTMLElement | null>(null);

// Apply motion to the elements (retaining the 3D spring effect)
const motionCard = useMotion(cardRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -6, y: -6 }, // Adjusted spring distance for product card look
}, {
  transition: springTransition,
});

const motionInner = useMotion(innerRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -6, y: -6 },
}, {
  transition: springTransition,
});

// --- Class Merging ---
const baseCardClass = "group w-full border-2 border-black bg-white";
const innerCardClass = "relative -m-0.5 border-2 border-black bg-white flex flex-col justify-between overflow-hidden";
const accentColor = 'bg-sky-200'; // Define a primary accent color for the block effect

// Computed property to merge classes (like twMerge)
const mergedBaseClass = computed(() => twMerge(baseCardClass, props.className));
const mergedInnerClass = computed(() => twMerge(innerCardClass, accentColor));
</script>

<template>
  <div
    ref="cardRef"
    @mouseenter="motionCard.apply('hovered')"
    @mouseleave="motionCard.apply('initial')"
    :class="mergedBaseClass"
  >
    <div
      ref="innerRef"
      @mouseenter="motionInner.apply('hovered')"
      @mouseleave="motionInner.apply('initial')"
      :class="mergedInnerClass"
    >
      <span v-if="isPopular" class="absolute top-3 left-3 z-10 inline-flex items-center rounded-full bg-red-600 px-3 py-0.5 text-xs font-bold text-white shadow-lg ring-1 ring-inset ring-red-700/50">
          🔥 POPULAR
      </span>

      <button
        class="absolute top-3 right-3 z-10 p-2 rounded-full bg-white border border-gray-300 transition-colors hover:bg-red-100 hover:text-red-600 shadow-md"
        aria-label="Add to favourites"
        @click.stop.prevent="$emit('favourite', product.id)"
      >
        <div v-html="IconStar" class="size-5"></div>
      </button>

      <div class="h-48 overflow-hidden bg-white flex items-center justify-center p-4">
        <img
          :src="imageUrl"
          :alt="'Image of ' + product.name"
          class="w-full h-full object-contain transition duration-500 group-hover:scale-105"
        />
      </div>

      <div class="p-4 border-t-2 border-black bg-white">
        <a :href="'/product/' + product.id" class="block">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">MPN: {{ product.mpn }}</p>
          <p class="flex items-center text-xl font-bold text-gray-900 transition truncate group-hover:text-sky-600 mt-1">
            <span class="mr-2">{{ product.name }}</span>
            <span class="inline-block transition-all duration-300 ease-in-out group-hover:translate-x-1" v-html="IconArrowRight"></span>
          </p>
        </a>

        <div class="mt-4 flex justify-between items-end">
            <p class="text-3xl font-black text-sky-600">
                £{{ product.cost.toFixed(2) }}
            </p>

            <button
                class="absolute -bottom-0.5 right-0.5 z-20 translate-y-full border-2 border-black bg-black text-white px-4 py-2 text-sm font-semibold opacity-0 transition-all duration-300 ease-in-out group-hover:translate-y-0 group-hover:opacity-100 group-hover:bg-sky-600 group-hover:text-white"
                @click="$emit('addToCart', product.id)"
            >
                ADD TO CART
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
          class="pointer-events-none absolute z-0 rounded-full text-gray-400 opacity-20"
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
