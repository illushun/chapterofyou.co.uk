<script setup lang="ts">
import { ref, onMounted, computed, watch, nextTick } from 'vue';
// Assuming 'cn' is a utility function for merging Tailwind classes,
// if you don't have one, you can use a simple array approach for classes in the template.
// For now, we'll assume it's imported or replaced with a simple function.
// If you are using Tailwind, you might not even need 'cn' for this usage.

// Simple placeholder for 'cn' if you don't have a specific utility
const cn = (baseClass: string, additionalClass: string | undefined): string => {
    return additionalClass ? `${baseClass} ${additionalClass}` : baseClass;
};

// --- TypeScript Interfaces ---
interface Item {
    quote: string;
    name: string;
    title: string;
}

interface Props {
    items: Item[];
    direction?: 'left' | 'right';
    speed?: 'fast' | 'normal' | 'slow';
    pauseOnHover?: boolean;
    className?: string;
}

const props = withDefaults(defineProps<Props>(), {
    direction: 'left',
    speed: 'fast',
    pauseOnHover: true,
    className: '',
});

// --- Refs (replacing React's useRef and useState) ---
const containerRef = ref<HTMLDivElement | null>(null);
const scrollerRef = ref<HTMLUListElement | null>(null);
const start = ref(false);

// --- Component Logic ---

/**
 * Duplicates the list items to enable continuous scrolling and sets up CSS variables.
 */
const addAnimation = () => {
    if (containerRef.value && scrollerRef.value) {
        const scrollerContent = Array.from(scrollerRef.value.children);

        // 1. Duplicate content
        scrollerContent.forEach((item) => {
            const duplicatedItem = item.cloneNode(true);
            scrollerRef.value?.appendChild(duplicatedItem);
        });

        // 2. Set direction and speed variables
        getDirection();
        getSpeed();

        // 3. Start animation
        start.value = true;
    }
};

const getDirection = () => {
    if (containerRef.value) {
        const directionValue = props.direction === 'left' ? 'forwards' : 'reverse';
        containerRef.value.style.setProperty('--animation-direction', directionValue);
    }
};

const getSpeed = () => {
    if (containerRef.value) {
        let duration = '40s';
        if (props.speed === 'fast') {
            duration = '20s';
        } else if (props.speed === 'slow') {
            duration = '80s';
        }
        containerRef.value.style.setProperty('--animation-duration', duration);
    }
};

// --- Lifecycle Hook (replacing React's useEffect) ---
onMounted(() => {
    // nextTick ensures the component is mounted and refs are accessible
    nextTick(() => {
        addAnimation();
    });
});
</script>

<template>
    <div
        :class="cn(
            'scroller relative z-20 max-w-7xl overflow-hidden [mask-image:linear-gradient(to_right,transparent,white_20%,white_80%,transparent)]',
            props.className,
        )"
        ref="containerRef"
    >
        <ul
            ref="scrollerRef"
            :class="[
                'flex w-max min-w-full shrink-0 flex-nowrap gap-4 py-4',
                {
                    'animate-scroll': start,
                    'hover:[animation-play-state:paused]': props.pauseOnHover
                }
            ]"
        >
            <li
                v-for="(item, idx) in props.items"
                :key="`original-${item.name}-${idx}`"
                class="relative w-[350px] max-w-full shrink-0 rounded-2xl border border-b-0 border-zinc-200 bg-[linear-gradient(180deg,#fafafa,#f5f5f5)] px-8 py-6 md:w-[450px] dark:border-zinc-700 dark:bg-[linear-gradient(180deg,#27272a,#18181b)]"
            >
                <blockquote>
                    <div
                        aria-hidden="true"
                        class="user-select-none pointer-events-none absolute -top-0.5 -left-0.5 -z-1 h-[calc(100%_+_4px)] w-[calc(100%_+_4px)]"
                    ></div>
                    <span class="relative z-20 text-sm leading-[1.6] font-normal text-neutral-800 dark:text-gray-100">
                        {{ item.quote }}
                    </span>
                    <div class="relative z-20 mt-6 flex flex-row items-center">
                        <span class="flex flex-col gap-1">
                            <span class="text-sm leading-[1.6] font-normal text-neutral-500 dark:text-gray-400">
                                {{ item.name }}
                            </span>
                            <span class="text-sm leading-[1.6] font-normal text-neutral-500 dark:text-gray-400">
                                {{ item.title }}
                            </span>
                        </span>
                    </div>
                </blockquote>
            </li>
            </ul>
    </div>
</template>

<style>

/* If you cannot modify tailwind.config.js, you must add the raw CSS: */
@keyframes scroll {
    to {
        /*
         * Moves the list left by half its width plus half the gap size (1rem gap = 0.5rem half gap).
         * This value is CRITICAL for smooth infinite loop after duplication.
         */
        transform: translate(calc(-50% - 0.5rem));
    }
}

.animate-scroll {
    animation: scroll var(--animation-duration, 40s) linear infinite var(--animation-direction, forwards);
}

.scroller {
    /* Set defaults in case JS is slow */
    --animation-direction: forwards;
    --animation-duration: 40s;
}

</style>
