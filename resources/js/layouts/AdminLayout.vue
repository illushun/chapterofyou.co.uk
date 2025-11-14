<script setup lang="ts">
import { Link, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { Route } from 'ziggy-js';

declare const route: Route;

const isRouteActive = (name: string): boolean => {
    return route().current(name);
};

const navLinks = [
    { name: 'Dashboard', route: 'admin.dashboard', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m15.356 2H21v-5' },
    { name: 'Products', route: 'admin.products.index', icon: 'M5 8h.01M19 8h.01M10 8h.01M5 12h.01M19 12h.01M10 12h.01M5 16h.01M19 16h.01M10 16h.01M21 12a9 9 0 01-9 9 9 9 0 01-9-9c0-4.97 4.03-9 9-9s9 4.03 9 9z' },
    { name: 'Relationships', route: 'admin.products.relationships', icon: 'M5 8h.01M19 8h.01M10 8h.01M5 12h.01M19 12h.01M10 12h.01M5 16h.01M19 16h.01M10 16h.01M21 12a9 9 0 01-9 9 9 9 0 01-9-9c0-4.97 4.03-9 9-9s9 4.03 9 9z' },
    { name: 'Categories', route: 'admin.categories.index', icon: 'M5 8h.01M19 8h.01M10 8h.01M5 12h.01M19 12h.01M10 12h.01M5 16h.01M19 16h.01M10 16h.01M21 12a9 9 0 01-9 9 9 9 0 01-9-9c0-4.97 4.03-9 9-9s9 4.03 9 9z' },
    { name: 'Reviews', route: 'admin.reviews.index', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' },
    { name: 'Orders', route: 'admin.orders.index', icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' },
    { name: 'Users', route: 'admin.users.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { name: 'Carts', route: 'admin.carts.index', icon: 'M3 10h18M7 15h10M4 10V7a3 3 0 013-3h10a3 3 0 013 3v3' },
    { name: 'CLP Labels', route: 'admin.clp-labels.index', icon: 'M3 10h18M7 15h10M4 10V7a3 3 0 013-3h10a3 3 0 013 3v3' },
    { name: 'View Website', route: 'home', icon: 'M10 19l-7-7m0 0l7-7m-7 7h18', external: true },
];

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const getTitle = computed(() => {
    // Dynamically generate title based on the current route name
    const currentRouteName = page.url.split('/').pop()?.split('?')[0];
    const match = navLinks.find(link => route(link.route) === page.url || route(link.route).includes(currentRouteName ?? ''));
    return match ? `Admin | ${match.name}` : 'Admin Dashboard';
});

// Utility to check if a route is active
/*const isActive = (routeName: string) => {
    return route().current(routeName) || page.url.includes(routeName.split('.')[1]);
};*/
</script>

<template>

    <Head :title="getTitle" />

    <div class="min-h-screen bg-background text-copy">

        <aside
            :class="{ 'w-64': true, 'fixed top-0 left-0 h-full hidden lg:block': true, 'p-4': true, 'border-r-2 border-copy': true }"
            style="background-color: var(--secondary);">
            <div class="sticky top-4">
                <div class="relative rounded-lg -m-0.5 p-4 bg-foreground border-2 border-copy">
                    <nav class="space-y-2">
                        <Link v-for="link in navLinks" :key="link.route" :href="route(link.route)" :class="[
                            'relative flex items-center gap-3 rounded-lg -m-0.5 px-4 py-2 transition-colors duration-200 border-2 border-copy text-copy',
                            isRouteActive(link.route)
                                ? 'bg-primary font-bold text-primary-content border-copy shadow-md'
                                : 'bg-foreground hover:bg-secondary-light',
                        ]">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon" />
                        </svg>
                        <span>{{ link.name }}</span>
                        </Link>
                    </nav>
                </div>
            </div>
        </aside>

        <div class="lg:pl-64">

            <header class="sticky top-0 z-40 p-4 border-b-2 border-copy bg-background shadow-md">
                <div
                    class="relative rounded-lg -m-0.5 p-3 bg-foreground border-2 border-copy flex justify-between items-center">
                    <button @click="sidebarOpen = true" class="lg:hidden text-copy hover:text-primary transition">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <h2 class="text-lg font-bold text-copy hidden sm:block">{{ getTitle.replace('Admin | ', '') }}</h2>

                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-copy-light hidden sm:inline-block">Welcome, {{ user?.name
                        }}</span>
                        <Link :href="route('logout')" method="post" as="button"
                            class="relative rounded-lg -m-0.5 px-3 py-1 text-sm font-medium text-error-content transition border-2 border-copy bg-error-light hover:bg-error-dark">
                        Logout
                        </Link>
                    </div>
                </div>
            </header>

            <main class="p-4 md:p-8">
                <div class="relative rounded-lg -m-0.5 p-6 bg-foreground border-2 border-copy min-h-[80vh]">
                    <slot />
                </div>
            </main>
        </div>

        <Transition name="slide-fade">
            <div v-if="sidebarOpen" @click.self="sidebarOpen = false"
                class="lg:hidden fixed inset-0 z-50 bg-black bg-opacity-70 transition-opacity">
                <div class="fixed inset-y-0 left-0 w-64 h-full p-4 border-r-2 border-copy shadow-2xl overflow-y-auto"
                    style="background-color: var(--secondary);">
                    <div class="relative rounded-lg -m-0.5 p-4 bg-foreground border-2 border-copy">
                        <div class="flex justify-between items-center mb-6 border-b-2 border-copy pb-2">
                            <h1 class="text-xl font-black text-primary-content" style="color: var(--primary);">
                                C.o.Y. Admin
                            </h1>
                            <button @click="sidebarOpen = false" class="text-copy-light hover:text-copy transition"
                                aria-label="Close menu">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <nav class="space-y-2">
                            <Link v-for="link in navLinks" :key="link.route" :href="route(link.route)"
                                @click="sidebarOpen = false" :class="[
                                    'relative flex items-center gap-3 rounded-lg -m-0.5 px-4 py-2 transition-colors duration-200 border-2 border-copy text-copy',
                                    isRouteActive(link.route)
                                        ? 'bg-primary font-bold text-primary-content border-copy shadow-md'
                                        : 'bg-foreground hover:bg-secondary-light',
                                ]">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="link.icon" />
                            </svg>
                            <span>{{ link.name }}</span>
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
/* Mobile Filter Slide-Over Transition Styles (Reusing the style from your product/View.vue) */
.slide-fade-enter-active {
    transition: opacity 0.5s ease;
}

.slide-fade-leave-active {
    transition: opacity 0.5s ease;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    opacity: 0;
}

.slide-fade-enter-active>div {
    transition: transform 0.5s ease;
}

.slide-fade-leave-active>div {
    transition: transform 0.5s ease;
}

.slide-fade-enter-from>div {
    transform: translateX(-100%);
}

.slide-fade-leave-to>div {
    transform: translateX(-100%);
}
</style>
