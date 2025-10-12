<script setup lang="ts">
import { ref as vueRef, watch, provide, computed } from 'vue';
import { useScroll } from '@vueuse/core';
import { IconMenu2, IconX } from '@tabler/icons-vue';
import { Transition } from 'vue'; // For AnimatePresence replacement

const cn = (baseClass: string, additionalClass: string | undefined): string => {
    return additionalClass ? `${baseClass} ${additionalClass}` : baseClass;
};

interface Item {
    name: string;
    link: string;
}
const NAV_STATE_KEY = Symbol('navState');

interface NavbarProps {
    className?: string;
}

const navbarProps = defineProps<NavbarProps>();

const navbarRef = vueRef<HTMLDivElement | null>(null);
const { y: scrollY } = useScroll({ target: navbarRef });

const visible = vueRef(false);

watch(scrollY, (latest) => {
    if (latest > 100) {
        visible.value = true;
    } else {
        visible.value = false;
    }
}, { immediate: true });

// Provide the `visible` state to all descendant components
provide(NAV_STATE_KEY, visible);

interface NavBodyProps {
    className?: string;
}

const NavBody = defineComponent({
    props: {
        className: String,
    },
    setup(props, { slots }) {
        const isVisible = inject<Ref<boolean>>(NAV_STATE_KEY, ref(false));

        const motionConfig = computed(() => ({
            initial: {
                backdropFilter: 'none',
                boxShadow: 'none',
                width: '100%',
                y: 0,
            },
            enter: {
                backdropFilter: isVisible.value ? 'blur(10px)' : 'none',
                boxShadow: isVisible.value
                    ? "0 0 24px rgba(34, 42, 53, 0.06), 0 1px 1px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(34, 42, 53, 0.04), 0 0 4px rgba(34, 42, 53, 0.08), 0 16px 68px rgba(47, 48, 55, 0.05), 0 1px 0 rgba(255, 255, 255, 0.1) inset"
                    : "none",
                width: isVisible.value ? "40%" : "100%",
                y: isVisible.value ? 20 : 0,
            },
            transition: {
                type: 'spring',
                stiffness: 200,
                damping: 50,
            },
        }));

        return () => h('div', {
            'v-motion': motionConfig.value,
            style: { minWidth: '800px' },
            class: cn(
                "relative z-[60] mx-auto hidden w-full max-w-7xl flex-row items-center justify-between self-start rounded-full bg-transparent px-4 py-2 lg:flex dark:bg-transparent",
                isVisible.value && "bg-white/80 dark:bg-neutral-950/80",
                props.className,
            ),
        }, slots.default?.());
    },
});

interface NavItemsProps {
    items: Item[];
    className?: string;
}

const NavItems = defineComponent({
    props: {
        items: { type: Array as PropType<Item[]>, required: true },
        className: String,
    },
    setup(props) {
        const hovered = ref<number | null>(null);

        return () => h('div', {
            // v-motion is on the container here, keeping it simple
            'v-motion': { initial: {}, enter: {} },
            onMouseleave: () => { hovered.value = null; },
            class: cn(
                "absolute inset-0 hidden flex-1 flex-row items-center justify-center space-x-2 text-sm font-medium text-zinc-600 transition duration-200 hover:text-zinc-800 lg:flex lg:space-x-2",
                props.className,
            ),
        }, props.items.map((item, idx) =>
            h('a', {
                onMouseenter: () => { hovered.value = idx; },
                class: 'relative px-4 py-2 text-neutral-600 dark:text-neutral-300',
                key: `link-${idx}`,
                href: item.link,
            }, [
                hovered.value === idx
                    ? h('div', {
                        class: 'absolute inset-0 h-full w-full rounded-full bg-gray-100 dark:bg-neutral-800 transition-all duration-300',
                        style: { zIndex: 10 }
                    })
                    : null,
                h('span', { class: 'relative z-20' }, item.name),
            ])
        ));
    },
});


const isOpen = ref(false); // State for mobile menu open/close

const MobileNav = defineComponent({
    props: {
        className: String,
    },
    setup(props, { slots }) {
        const isVisible = inject<Ref<boolean>>(NAV_STATE_KEY, ref(false));

        const motionConfig = computed(() => ({
            initial: {},
            enter: {
                backdropFilter: isVisible.value ? 'blur(10px)' : 'none',
                boxShadow: isVisible.value
                    ? "0 0 24px rgba(34, 42, 53, 0.06), 0 1px 1px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(34, 42, 53, 0.04), 0 0 4px rgba(34, 42, 53, 0.08), 0 16px 68px rgba(47, 48, 55, 0.05), 0 1px 0 rgba(255, 255, 255, 0.1) inset"
                    : "none",
                width: isVisible.value ? "90%" : "100%",
                paddingRight: isVisible.value ? "12px" : "0px",
                paddingLeft: isVisible.value ? "12px" : "0px",
                borderRadius: isVisible.value ? "4px" : "2rem",
                y: isVisible.value ? 20 : 0,
            },
            transition: {
                type: 'spring',
                stiffness: 200,
                damping: 50,
            },
        }));

        return () => h('div', {
            'v-motion': motionConfig.value,
            class: cn(
                "relative z-50 mx-auto flex w-full max-w-[calc(100vw-2rem)] flex-col items-center justify-between bg-transparent px-0 py-2 lg:hidden",
                isVisible.value && "bg-white/80 dark:bg-neutral-950/80",
                props.className,
            ),
        }, slots.default?.());
    },
});

const MobileNavHeader = defineComponent({
    props: {
        className: String,
    },
    setup(props, { slots }) {
        return () => h('div', {
            class: cn(
                "flex w-full flex-row items-center justify-between",
                props.className,
            ),
        }, slots.default?.());
    }
});

const MobileNavMenu = defineComponent({
    props: {
        className: String,
        isOpen: { type: Boolean, required: true },
    },
    setup(props, { slots }) {
        return () => h(Transition, {
            name: 'mobile-menu-fade',
            enterActiveClass: 'transition-opacity duration-300',
            leaveActiveClass: 'transition-opacity duration-300',
            enterFromClass: 'opacity-0',
            leaveToClass: 'opacity-0',
        }, () => props.isOpen ? h('div', {
            class: cn(
                "absolute inset-x-0 top-16 z-50 flex w-full flex-col items-start justify-start gap-4 rounded-lg bg-white px-4 py-8 shadow-[0_0_24px_rgba(34,_42,_53,_0.06),_0_1px_1px_rgba(0,_0,_0,_0.05),_0_0_0_1px_rgba(34,_42,_53,_0.04),_0_0_4px_rgba(34,_42,_53,_0.08),_0_16px_68px_rgba(47,_48,_55,_0.05),_0_1px_0_rgba(255,_255,_255,_0.1)_inset] dark:bg-neutral-950",
                props.className,
            ),
        }, slots.default?.()) : null);
    }
});

const MobileNavToggle = defineComponent({
    props: {
        isOpen: { type: Boolean, required: true },
    },
    setup(props) {
        return () => props.isOpen
            ? h(IconX, {
                class: 'text-black dark:text-white',
                onClick: () => isOpen.value = false
            })
            : h(IconMenu2, {
                class: 'text-black dark:text-white',
                onClick: () => isOpen.value = true
            });
    }
});

const NavbarLogo = defineComponent({
    setup() {
        return () => h('a', {
            href: "#",
            class: "relative z-20 mr-4 flex items-center space-x-2 px-2 py-1 text-sm font-normal text-black",
        }, [
            h('img', {
                src: "https://assets.aceternity.com/logo-dark.png",
                alt: "logo",
                width: 30,
                height: 30
            }),
            h('span', { class: "font-medium text-black dark:text-white" }, "Startup"),
        ]);
    }
});

const NavbarButton = defineComponent({
    props: {
        href: String,
        as: { type: [String, Object], default: 'a' },
        className: String,
        variant: { type: String, default: 'primary', validator: (v: string) => ['primary', 'secondary', 'dark', 'gradient'].includes(v) },
    },
    setup(props, { slots }) {
        const baseStyles = "px-4 py-2 rounded-md bg-white button bg-white text-black text-sm font-bold relative cursor-pointer hover:-translate-y-0.5 transition duration-200 inline-block text-center";

        const variantStyles: Record<string, string> = {
            primary: "shadow-[0_0_24px_rgba(34,_42,_53,_0.06),_0_1px_1px_rgba(0,_0,_0,_0.05),_0_0_0_1px_rgba(34,_42,_53,_0.04),_0_0_4px_rgba(34,_42,_53,_0.08),_0_16px_68px_rgba(47,_48,_55,_0.05),_0_1px_0_rgba(255,_255,_255,_0.1)_inset]",
            secondary: "bg-transparent shadow-none dark:text-white",
            dark: "bg-black text-white shadow-[0_0_24px_rgba(34,_42,_53,_0.06),_0_1px_1px_rgba(0,_0,_0,_0.05),_0_0_0_1px_rgba(34,_42,_53,_0.04),_0_0_4px_rgba(34,_42,_53,_0.08),_0_16px_68px_rgba(47,_48,_55,_0.05),_0_1px_0_rgba(255,_255,_255,_0.1)_inset]",
            gradient: "bg-gradient-to-b from-blue-500 to-blue-700 text-white shadow-[0px_2px_0px_0px_rgba(255,255,255,0.3)_inset]",
        };

        const Tag = props.as;

        return () => h(Tag, {
            href: props.href,
            class: cn(baseStyles, variantStyles[props.variant], props.className),
            ...props
        }, slots.default?.());
    }
});
</script>

<template>
    <div
        ref="navbarRef"
        :class="cn('sticky inset-x-0 top-20 z-40 w-full', navbarProps.className)"
        v-motion
        :initial="{}"
        :enter="{}"
    >
        <slot :visible="visible" />
    </div>

    <component :is="NavBody" />
    <component :is="NavItems" />
    <component :is="MobileNav" />
    <component :is="MobileNavHeader" />
    <component :is="MobileNavMenu" />
    <component :is="MobileNavToggle" />
    <component :is="NavbarLogo" />
    <component :is="NavbarButton" />
</template>
