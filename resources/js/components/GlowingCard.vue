<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed, nextTick } from 'vue';
import { animate } from 'motion'; // Use the core motion library
import { isRef, type Ref } from 'vue'; // Import types for correct usage

// NOTE: You need to implement this utility function (e.g., in a separate file or inline)
// This is assumed to be a Tailwind CSS class utility.
const cn = (baseClass: string, additionalClass?: string | Ref<string | boolean> | null): string => {
    let classes = baseClass;
    if (additionalClass) {
        if (isRef(additionalClass)) {
            if (additionalClass.value) {
                classes += ` ${additionalClass.value}`;
            }
        } else if (typeof additionalClass === 'string') {
            classes += ` ${additionalClass}`;
        }
    }
    return classes;
};

// --- Props (Type is defined separately for clarity) ---
interface GlowingEffectProps {
    blur?: number;
    inactiveZone?: number;
    proximity?: number;
    spread?: number;
    variant?: "default" | "white";
    glow?: boolean;
    className?: string;
    disabled?: boolean;
    movementDuration?: number;
    borderWidth?: number;
}

const props = withDefaults(defineProps<GlowingEffectProps>(), {
    blur: 0,
    inactiveZone: 0.7,
    proximity: 0,
    spread: 20,
    variant: "default",
    glow: false,
    movementDuration: 2,
    borderWidth: 1,
    disabled: true, // IMPORTANT: The original component defaults to disabled: true
});

// --- State and Refs ---
const containerRef = ref<HTMLDivElement | null>(null);
const lastPosition = ref({ x: 0, y: 0 });
const animationFrameId = ref<number>(0);

// --- Methods ---

// Replaces useCallback and useEffect cleanup
const handleMove = (e?: MouseEvent | { x: number; y: number }) => {
    const element = containerRef.value;
    if (!element || props.disabled) return;

    if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
    }

    animationFrameId.value = requestAnimationFrame(() => {
        const { left, top, width, height } = element.getBoundingClientRect();
        const mouseX = e?.x ?? lastPosition.value.x;
        const mouseY = e?.y ?? lastPosition.value.y;

        if (e) {
            lastPosition.value = { x: mouseX, y: mouseY };
        }

        const center = [left + width * 0.5, top + height * 0.5];
        const distanceFromCenter = Math.hypot(
            mouseX - center[0],
            mouseY - center[1]
        );
        const inactiveRadius = 0.5 * Math.min(width, height) * props.inactiveZone;

        // 1. Inactive Zone Check
        if (distanceFromCenter < inactiveRadius) {
            element.style.setProperty("--active", "0");
            return;
        }

        // 2. Proximity Check
        const isActive =
            mouseX > left - props.proximity &&
            mouseX < left + width + props.proximity &&
            mouseY > top - props.proximity &&
            mouseY < top + height + props.proximity;

        element.style.setProperty("--active", isActive ? "1" : "0");

        if (!isActive) return;

        // 3. Calculate and Animate Angle
        const currentAngle =
            parseFloat(element.style.getPropertyValue("--start")) || 0;
        let targetAngle =
            (180 * Math.atan2(mouseY - center[1], mouseX - center[0])) /
                Math.PI +
            90;

        const angleDiff = ((targetAngle - currentAngle + 180) % 360) - 180;
        const newAngle = currentAngle + angleDiff;

        // Use core motion animate function
        animate(currentAngle, newAngle, {
            duration: props.movementDuration,
            ease: [0.16, 1, 0.3, 1],
            onUpdate: (value) => {
                element.style.setProperty("--start", String(value));
            },
        });
    });
};

// --- Lifecycle Hooks (Replacing useEffect) ---
onMounted(() => {
    // Only run if not disabled initially
    if (props.disabled) return;

    // Defer adding listeners until the DOM is ready
    nextTick(() => {
        setupListeners();
    });
});

onUnmounted(() => {
    // Cleanup requestAnimationFrame
    if (animationFrameId.value) {
        cancelAnimationFrame(animationFrameId.value);
    }
    // Cleanup event listeners
    removeListeners();
});

// Watch for changes in the `disabled` prop to toggle listeners
watch(() => props.disabled, (isDisabled) => {
    if (isDisabled) {
        removeListeners();
    } else {
        setupListeners();
    }
});

const handleScroll = () => handleMove();
const handlePointerMove = (e: PointerEvent) => handleMove(e);

const setupListeners = () => {
    window.addEventListener("scroll", handleScroll, { passive: true });
    document.body.addEventListener("pointermove", handlePointerMove, {
        passive: true,
    });
};

const removeListeners = () => {
    window.removeEventListener("scroll", handleScroll);
    document.body.removeEventListener("pointermove", handlePointerMove);
};


// --- Computed Styles (Replaces inline React.CSSProperties) ---
const customStyles = computed(() => ({
    "--blur": `${props.blur}px`,
    "--spread": props.spread,
    "--start": "0",
    "--active": "0",
    "--glowingeffect-border-width": `${props.borderWidth}px`,
    "--repeating-conic-gradient-times": "5",
    "--gradient":
        props.variant === "white"
            ? `repeating-conic-gradient(
                from 236.84deg at 50% 50%,
                var(--black),
                var(--black) calc(25% / var(--repeating-conic-gradient-times))
              )`
            : `radial-gradient(circle, #dd7bbb 10%, #dd7bbb00 20%),
                radial-gradient(circle at 40% 40%, #d79f1e 5%, #d79f1e00 15%),
                radial-gradient(circle at 60% 60%, #5a922c 10%, #5a922c00 20%),
                radial-gradient(circle at 40% 60%, #4c7894 10%, #4c789400 20%),
                repeating-conic-gradient(
                  from 236.84deg at 50% 50%,
                  #dd7bbb 0%,
                  #d79f1e calc(25% / var(--repeating-conic-gradient-times)),
                  #5a922c calc(50% / var(--repeating-conic-gradient-times)),
                  #4c7894 calc(75% / var(--repeating-conic-gradient-times)),
                  #dd7bbb calc(100% / var(--repeating-conic-gradient-times))
                )`,
}));

// The component exposes the default slot for its children
</script>

<template>
    <slot>
        <div class="absolute inset-0 z-0">
            <div
                :class="cn(
                    'pointer-events-none absolute -inset-px hidden rounded-[inherit] border opacity-0 transition-opacity',
                    props.glow && 'opacity-100',
                    props.variant === 'white' && 'border-white',
                    props.disabled && '!block'
                )"
            />

            <div
                ref="containerRef"
                :style="customStyles as any"
                :class="cn(
                    'pointer-events-none absolute inset-0 rounded-[inherit] opacity-100 transition-opacity',
                    props.glow && 'opacity-100',
                    props.blur > 0 && 'blur-[var(--blur)] ',
                    props.className,
                    props.disabled && '!hidden'
                )"
            >
                <div
                    :class="cn(
                        'glow',
                        'rounded-[inherit]',
                        'after:content-[\' \'] after:rounded-[inherit] after:absolute after:inset-[calc(-1*var(--glowingeffect-border-width))]',
                        'after:[border:var(--glowingeffect-border-width)_solid_transparent]',
                        'after:[background:var(--gradient)] after:[background-attachment:fixed]',
                        'after:opacity-[var(--active)] after:transition-opacity after:duration-300',
                        'after:[mask-clip:padding-box,border-box]',
                        'after:[mask-composite:intersect]',
                        'after:[mask-image:linear-gradient(#0000,#0000),conic-gradient(from_calc((var(--start)-var(--spread))*1deg),#00000000_0deg,#fff,#00000000_calc(var(--spread)*2deg))]'
                    )"
                />
            </div>
        </div>
    </slot>
</template>

<style scoped>
/* Define the custom properties for global use if needed, or rely on inline style */
/* The glow element's after pseudo-element handles the magic */
</style>
