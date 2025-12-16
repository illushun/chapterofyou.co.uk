<script setup lang="ts">
import { Link, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { Route } from 'ziggy-js';

import DashboardIcon from '@/components/icons/DashboardIcon.vue'
import ProductsIcon from '@/components/icons/ProductsIcon.vue'
import RelationshipsIcon from '@/components/icons/RelationshipsIcon.vue'
import CategoriesIcon from '@/components/icons/CategoriesIcon.vue'
import ReviewsIcon from '@/components/icons/ReviewsIcon.vue'
import MessagesIcon from '@/components/icons/MessagesIcon.vue'
import OrdersIcon from '@/components/icons/OrdersIcon.vue'
import CouriersIcon from '@/components/icons/CouriersIcon.vue'
import UsersIcon from '@/components/icons/UsersIcon.vue'
import CartsIcon from '@/components/icons/CartsIcon.vue'
import ClpLabelsIcon from '@/components/icons/ClpLabelsIcon.vue'
import ViewWebsiteIcon from '@/components/icons/ViewWebsiteIcon.vue'

declare const route: Route;

const isRouteActive = (name: string): boolean => {
    return route().current(name);
};

const navLinks = [
    { name: 'Dashboard', route: 'admin.dashboard', icon: DashboardIcon },
    { name: 'Products', route: 'admin.products.index', icon: ProductsIcon },
    { name: 'Relationships', route: 'admin.products.relationships', icon: RelationshipsIcon },
    { name: 'Categories', route: 'admin.categories.index', icon: CategoriesIcon },
    { name: 'Reviews', route: 'admin.reviews.index', icon: ReviewsIcon },
    { name: 'Messages', route: 'admin.messages.index', icon: MessagesIcon },
    { name: 'Orders', route: 'admin.orders.index', icon: OrdersIcon },
    { name: 'Couriers', route: 'admin.couriers.index', icon: CouriersIcon },
    { name: 'Users', route: 'admin.users.index', icon: UsersIcon },
    { name: 'Carts', route: 'admin.carts.index', icon: CartsIcon },
    { name: 'CLP Labels', route: 'admin.clp-labels.index', icon: ClpLabelsIcon },
    { name: 'View Website', route: 'home', icon: ViewWebsiteIcon, external: true },
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
                <div class="relative rounded-lg mb-2 -m-0.5 p-4 bg-foreground border-2 border-copy">
                    <nav class="space-y-2">
                        <Link v-for="link in navLinks" :key="link.route" :href="route(link.route)" :class="[
                            'relative flex items-center gap-3 rounded-lg -m-0.5 px-4 py-2 transition-colors duration-200 border-2 border-copy text-copy',
                            isRouteActive(link.route)
                                ? 'bg-primary font-bold text-primary-content border-copy shadow-md'
                                : 'bg-foreground hover:bg-secondary-light',
                        ]">

                        <component :is="link.icon"></component>
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
                            <h1 class="text-xl font-black text-primary-content">
                                Admin Panel
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
                                    'relative flex items-center mb-2 gap-3 rounded-lg -m-0.5 px-4 py-2 transition-colors duration-200 border-2 border-copy text-copy',
                                    isRouteActive(link.route)
                                        ? 'bg-primary font-bold text-primary-content border-copy shadow-md'
                                        : 'bg-foreground hover:bg-secondary-light',
                                ]">

                            <component :is="link.icon"></component>
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
