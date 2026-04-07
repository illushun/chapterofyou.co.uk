<script setup lang="ts">
import { Link, Head, usePage, router } from '@inertiajs/vue3';
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

const isRouteActive = (name: string): boolean => route().current(name);

const navGroups = [
    {
        label: 'Overview',
        accent: '#f2c4ce',
        links: [
            { name: 'Dashboard', route: 'admin.dashboard', icon: DashboardIcon },
            { name: 'Orders', route: 'admin.orders.index', icon: OrdersIcon },
            { name: 'Carts', route: 'admin.carts.index', icon: CartsIcon },
            { name: 'Users', route: 'admin.users.index', icon: UsersIcon },
        ],
    },
    {
        label: 'Catalogue',
        accent: '#c9b8f0',
        links: [
            { name: 'Products', route: 'admin.products.index', icon: ProductsIcon },
            { name: 'Relationships', route: 'admin.products.relationships', icon: RelationshipsIcon },
            { name: 'Categories', route: 'admin.categories.index', icon: CategoriesIcon },
            { name: 'Couriers', route: 'admin.couriers.index', icon: CouriersIcon },
        ],
    },
    {
        label: 'Engagement',
        accent: '#b8d9b8',
        links: [
            { name: 'Reviews', route: 'admin.reviews.index', icon: ReviewsIcon },
            { name: 'Messages', route: 'admin.messages.index', icon: MessagesIcon },
            { name: 'Wishlists', route: 'admin.wishlists.index', icon: OrdersIcon },
            { name: 'Vouchers', route: 'admin.vouchers.index', icon: OrdersIcon },
            { name: 'Broadcast', route: 'admin.broadcasts.index', icon: MessagesIcon },
        ],
    },
    {
        label: 'Production',
        accent: '#f5d5b8',
        links: [
            { name: 'Oils', route: 'admin.oils.index', icon: ClpLabelsIcon },
            { name: 'CLP Labels', route: 'admin.clp-labels.index', icon: ClpLabelsIcon },
            { name: 'Batch Sheets', route: 'admin.batch-sheets.index', icon: ClpLabelsIcon },
        ],
    },
];

const navLinks = navGroups.flatMap(g => g.links);
const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const firstName = computed(() => user.value?.name?.split(' ')[0] ?? 'Admin');
const sidebarOpen = ref(false);

const pageTitle = computed(() => navLinks.find(l => isRouteActive(l.route))?.name ?? 'Dashboard');

const activeAccent = computed(() => {
    for (const g of navGroups) {
        if (g.links.some(l => isRouteActive(l.route))) return g.accent;
    }
    return '#f2c4ce';
});
</script>

<template>

    <Head :title="`${pageTitle} — Admin`" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap"
        rel="stylesheet" />

    <div class="al-root">

        <!-- ── Desktop sidebar ── -->
        <aside class="al-sidebar">
            <!-- Brand -->
            <div class="al-brand">
                <div class="al-brand-logo">COY</div>
                <div>
                    <p class="al-brand-name">Chapter of You</p>
                    <p class="al-brand-sub">Admin Console</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="al-nav">
                <template v-for="group in navGroups" :key="group.label">
                    <p class="al-group-label" :style="`color: ${group.accent}`">{{ group.label }}</p>
                    <Link v-for="link in group.links" :key="link.route" :href="route(link.route)" class="al-link"
                        :class="{ 'al-link--active': isRouteActive(link.route) }" :style="isRouteActive(link.route)
                            ? `border-left-color:${group.accent}; background:color-mix(in srgb,${group.accent} 18%,transparent)`
                            : ''">
                    <component :is="link.icon" class="al-link-icon" />
                    {{ link.name }}
                    </Link>
                </template>
            </nav>

            <!-- Footer -->
            <div class="al-sidebar-foot">
                <Link :href="route('home')" class="al-ext-link">
                <component :is="ViewWebsiteIcon" class="al-link-icon" />
                View website
                </Link>
                <div class="al-user-chip">
                    <div class="al-avatar">{{ firstName.charAt(0).toUpperCase() }}</div>
                    <span class="al-user-name">{{ firstName }}</span>
                </div>
            </div>
        </aside>

        <!-- ── Main ── -->
        <div class="al-main">

            <!-- Topbar -->
            <header class="al-topbar">
                <button @click="sidebarOpen = true" class="al-hamburger" aria-label="Open menu">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Active accent strip -->
                <span class="al-topbar-dot" :style="`background:${activeAccent}`"></span>
                <span class="al-topbar-page">{{ pageTitle }}</span>

                <div class="al-topbar-right">
                    <span class="al-topbar-greeting">Hi, {{ firstName }} ✦</span>
                    <Link :href="route('logout')" method="post" as="button" class="al-logout-btn">
                    Sign out
                    </Link>
                </div>
            </header>

            <!-- Page -->
            <main class="al-content">
                <slot />
            </main>

        </div>

        <!-- ── Mobile drawer ── -->
        <Transition name="al-backdrop">
            <div v-if="sidebarOpen" class="al-drawer-bg" @click.self="sidebarOpen = false">
                <Transition name="al-drawer">
                    <div v-if="sidebarOpen" class="al-drawer">

                        <div class="al-drawer-head">
                            <div class="al-brand" style="padding:0; border:none; background:none;">
                                <div class="al-brand-logo">COY</div>
                                <p class="al-brand-name" style="color:#fff">Chapter of You</p>
                            </div>
                            <button @click="sidebarOpen = false" class="al-close-btn" aria-label="Close">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round">
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <nav class="al-nav">
                            <template v-for="group in navGroups" :key="group.label">
                                <p class="al-group-label" :style="`color:${group.accent}`">{{ group.label }}</p>
                                <Link v-for="link in group.links" :key="link.route" :href="route(link.route)"
                                    @click="sidebarOpen = false" class="al-link"
                                    :class="{ 'al-link--active': isRouteActive(link.route) }" :style="isRouteActive(link.route)
                                        ? `border-left-color:${group.accent}; background:color-mix(in srgb,${group.accent} 18%,transparent)`
                                        : ''">
                                <component :is="link.icon" class="al-link-icon" />
                                {{ link.name }}
                                </Link>
                            </template>
                        </nav>

                    </div>
                </Transition>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
.al-root {
    --bb-navy: #1a1a2e;
    --bb-navy-m: #252542;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;

    display: flex;
    min-height: 100vh;
    background: var(--bb-cream);
    color: var(--bb-text);
    font-family: 'DM Sans', sans-serif;
}

/* ── Sidebar ── */
.al-sidebar {
    width: 244px;
    flex-shrink: 0;
    background: var(--bb-navy);
    display: none;
    flex-direction: column;
    position: fixed;
    inset-y: 0;
    left: 0;
    overflow-y: auto;
    z-index: 30;
    background-image:
        repeating-linear-gradient(135deg,
            transparent 0px, transparent 30px,
            rgba(255, 255, 255, 0.018) 30px, rgba(255, 255, 255, 0.018) 60px);
}

@media (min-width: 1024px) {
    .al-sidebar {
        display: flex;
    }
}

/* Brand */
.al-brand {
    padding: 1.4rem 1.25rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    flex-shrink: 0;
}

.al-brand-logo {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #f2c4ce;
    color: var(--bb-navy);
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.al-brand-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.2;
}

.al-brand-sub {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.38);
    margin-top: 0.1rem;
}

/* Nav */
.al-nav {
    flex: 1;
    padding: 1rem 0.8rem 0.5rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    min-height: 0;
}

.al-group-label {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    padding: 0.85rem 0.65rem 0.35rem;
    opacity: 0.85;
}

.al-link {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.58rem 0.75rem;
    font-size: 0.83rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.58);
    text-decoration: none;
    border-radius: 8px;
    border-left: 3px solid transparent;
    margin-bottom: 1px;
    transition: background 0.15s, color 0.15s;
}

.al-link:hover {
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.88);
}

.al-link--active {
    color: #fff;
    font-weight: 600;
}

.al-link-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    opacity: 0.75;
}

.al-link--active .al-link-icon {
    opacity: 1;
}

/* Sidebar footer */
.al-sidebar-foot {
    padding: 1rem 1.1rem 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    flex-shrink: 0;
}

.al-ext-link {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.78rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.38);
    text-decoration: none;
    transition: color 0.15s;
}

.al-ext-link:hover {
    color: rgba(255, 255, 255, 0.75);
}

.al-user-chip {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.55rem 0.75rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
}

.al-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #f2c4ce;
    color: var(--bb-navy);
    font-size: 0.7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.al-user-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.75);
}

/* ── Main ── */
.al-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

@media (min-width: 1024px) {
    .al-main {
        margin-left: 244px;
    }
}

/* ── Topbar ── */
.al-topbar {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(250, 249, 247, 0.9);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--bb-border);
    padding: 0.85rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.al-hamburger {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    color: var(--bb-text);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
    flex-shrink: 0;
}

.al-hamburger:hover {
    background: var(--bb-border);
}

@media (min-width: 1024px) {
    .al-hamburger {
        display: none;
    }
}

.al-topbar-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
    display: none;
}

@media (min-width: 640px) {
    .al-topbar-dot {
        display: block;
    }
}

.al-topbar-page {
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--bb-text);
    flex: 1;
}

.al-topbar-right {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-shrink: 0;
}

.al-topbar-greeting {
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--bb-muted);
    display: none;
}

@media (min-width: 640px) {
    .al-topbar-greeting {
        display: block;
    }
}

.al-logout-btn {
    padding: 0.4rem 0.85rem;
    border-radius: 6px;
    border: 1px solid var(--bb-border);
    background: var(--bb-surface);
    color: var(--bb-muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.al-logout-btn:hover {
    background: var(--bb-red);
    border-color: var(--bb-red);
    color: #fff;
}

/* ── Content ── */
.al-content {
    flex: 1;
    padding: 2rem 1.5rem;
}

@media (min-width: 768px) {
    .al-content {
        padding: 2rem 2.5rem;
    }
}

/* ── Mobile drawer ── */
.al-drawer-bg {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(26, 26, 46, 0.42);
    backdrop-filter: blur(4px);
}

.al-drawer {
    position: absolute;
    inset-y: 0;
    left: 0;
    width: 256px;
    background: var(--bb-navy);
    background-image: repeating-linear-gradient(135deg,
            transparent 0px, transparent 30px,
            rgba(255, 255, 255, 0.018) 30px, rgba(255, 255, 255, 0.018) 60px);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.al-drawer-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.15rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    flex-shrink: 0;
}

.al-close-btn {
    width: 30px;
    height: 30px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: transparent;
    color: rgba(255, 255, 255, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
}

.al-close-btn:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
}

/* Transitions */
.al-backdrop-enter-active,
.al-backdrop-leave-active {
    transition: opacity 0.2s;
}

.al-backdrop-enter-from,
.al-backdrop-leave-to {
    opacity: 0;
}

.al-drawer-enter-active,
.al-drawer-leave-active {
    transition: transform 0.24s ease;
}

.al-drawer-enter-from,
.al-drawer-leave-to {
    transform: translateX(-100%);
}
</style>
