<script setup lang="ts">
import { twMerge } from 'tailwind-merge';
import { computed, ref } from 'vue'; // ref is now imported for the template refs
import { useMotion } from '@vueuse/motion';

interface CardProps {
  title: string;
  subtitle: string;
  className?: string;
}

// Define Props with TypeScript interface
const props = defineProps<CardProps>();

// --- Animation Setup using @vueuse/motion (Composition API) ---

// Define the spring transition
const springTransition = {
  type: 'spring',
  stiffness: 200,
  damping: 10,
  mass: 1,
};

// Template Refs for Motion
const cardRef = ref<HTMLElement | null>(null);
const innerRef = ref<HTMLElement | null>(null);
const contentRef = ref<HTMLElement | null>(null);

// Apply motion to the elements using the same logic as Framer Motion's variants (x:-8, y:-8)
const motionCard = useMotion(cardRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -8, y: -8 },
}, {
  transition: springTransition,
});

const motionInner = useMotion(innerRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -8, y: -8 },
}, {
  transition: springTransition,
});

const motionContent = useMotion(contentRef, {
  initial: { x: 0, y: 0 },
  hovered: { x: -8, y: -8 },
}, {
  transition: springTransition,
});

// --- Class Merging (replacing twMerge) ---
const baseCardClass = "group w-full border-2 border-black bg-emerald-300";
const innerCardClass = "-m-0.5 border-2 border-black bg-emerald-300";
const contentClass = "relative -m-0.5 flex h-72 flex-col justify-between overflow-hidden border-2 border-black bg-emerald-300 p-8";

// Computed properties to merge classes (like twMerge)
const mergedBaseClass = computed(() => twMerge(baseCardClass, props.className));
const mergedInnerClass = computed(() => twMerge(innerCardClass, props.className));
const mergedContentClass = computed(() => twMerge(contentClass, props.className));
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
      <div
        ref="contentRef"
        @mouseenter="motionContent.apply('hovered')"
        @mouseleave="motionContent.apply('initial')"
        :class="mergedContentClass"
      >
        <p class="flex items-center text-2xl font-medium uppercase">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="w-6 h-6 -ml-8 mr-2 opacity-0 transition-all duration-300 ease-in-out group-hover:ml-0 group-hover:opacity-100"
          >
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
          {{ props.title }}
        </p>

        <div>
          <p class="transition-[margin] duration-300 ease-in-out group-hover:mb-10">
            {{ props.subtitle }}
          </p>
          <button
            class="absolute bottom-2 left-2 right-2 translate-y-full border-2 border-black bg-white px-4 py-2 text-black opacity-0 transition-all duration-300 ease-in-out group-hover:translate-y-0 group-hover:opacity-100"
          >
            LET'S GO
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
