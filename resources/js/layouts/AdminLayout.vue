<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { LogOut, Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import CartsIcon from '@/components/icons/CartsIcon.vue';
import CategoriesIcon from '@/components/icons/CategoriesIcon.vue';
import ClpLabelsIcon from '@/components/icons/ClpLabelsIcon.vue';
import CouriersIcon from '@/components/icons/CouriersIcon.vue';
import DashboardIcon from '@/components/icons/DashboardIcon.vue';
import MessagesIcon from '@/components/icons/MessagesIcon.vue';
import OrdersIcon from '@/components/icons/OrdersIcon.vue';
import ProductsIcon from '@/components/icons/ProductsIcon.vue';
import RelationshipsIcon from '@/components/icons/RelationshipsIcon.vue';
import ReviewsIcon from '@/components/icons/ReviewsIcon.vue';
import UsersIcon from '@/components/icons/UsersIcon.vue';
import ViewWebsiteIcon from '@/components/icons/ViewWebsiteIcon.vue';

const isRouteActive = (name: string): boolean => {
    if (route().current(name)) return true;
    if (name === 'admin.marketplace.etsy.index') {
        return !!route().current('admin.marketplace.etsy.*');
    }
    if (name === 'admin.finance.index') {
        return !!route().current('admin.finance.*');
    }
    return false;
};

const navGroups = [
    {
        label: 'Overview',
        links: [
            {
                name: 'Dashboard',
                route: 'admin.dashboard',
                icon: DashboardIcon,
            },
            { name: 'Orders', route: 'admin.orders.index', icon: OrdersIcon },
            { name: 'Carts', route: 'admin.carts.index', icon: CartsIcon },
            { name: 'Users', route: 'admin.users.index', icon: UsersIcon },
        ],
    },
    {
        label: 'Catalogue',
        links: [
            {
                name: 'Products',
                route: 'admin.products.index',
                icon: ProductsIcon,
            },
            {
                name: 'Relationships',
                route: 'admin.products.relationships',
                icon: RelationshipsIcon,
            },
            {
                name: 'Categories',
                route: 'admin.categories.index',
                icon: CategoriesIcon,
            },
            {
                name: 'Couriers',
                route: 'admin.couriers.index',
                icon: CouriersIcon,
            },
        ],
    },
    {
        label: 'Engagement',
        links: [
            {
                name: 'Reviews',
                route: 'admin.reviews.index',
                icon: ReviewsIcon,
            },
            {
                name: 'Messages',
                route: 'admin.messages.index',
                icon: MessagesIcon,
            },
            {
                name: 'Wishlists',
                route: 'admin.wishlists.index',
                icon: OrdersIcon,
            },
            {
                name: 'Vouchers',
                route: 'admin.vouchers.index',
                icon: OrdersIcon,
            },
            {
                name: 'Gift Vouchers',
                route: 'admin.gift-vouchers.index',
                icon: OrdersIcon,
            },
            {
                name: 'Broadcast',
                route: 'admin.broadcasts.index',
                icon: MessagesIcon,
            },
            {
                name: 'Journal',
                route: 'admin.journal.index',
                icon: MessagesIcon,
            },
            {
                name: 'Journal Auto Generator',
                route: 'admin.journal.auto-generator.edit',
                icon: MessagesIcon,
            },
        ],
    },
    {
        label: 'Production',
        links: [
            { name: 'Oils', route: 'admin.oils.index', icon: ClpLabelsIcon },
            {
                name: 'CLP Labels',
                route: 'admin.clp-labels.index',
                icon: ClpLabelsIcon,
            },
            {
                name: 'Batch Sheets',
                route: 'admin.batch-sheets.index',
                icon: ClpLabelsIcon,
            },
        ],
    },
    {
        label: 'Finance',
        links: [
            {
                name: 'Cost Items',
                route: 'admin.finance.index',
                icon: ClpLabelsIcon,
            },
            {
                name: 'Product Costs',
                route: 'admin.finance.products',
                icon: ProductsIcon,
            },
        ],
    },
    {
        label: 'Marketplaces',
        links: [
            {
                name: 'Etsy',
                route: 'admin.marketplace.etsy.index',
                icon: OrdersIcon,
            },
        ],
    },
];

const navLinks = navGroups.flatMap((g) => g.links);
const page = usePage();
const user = computed(() => (page.props as any).auth?.user);
const firstName = computed(() => user.value?.name?.split(' ')[0] ?? 'Admin');
const sidebarOpen = ref(false);

const pageTitle = computed(
    () => navLinks.find((l) => isRouteActive(l.route))?.name ?? 'Dashboard',
);
</script>

<template>
    <Head :title="`${pageTitle} : Admin`" />

    <div class="admin-root al-root">
        <!-- Desktop sidebar -->
        <aside class="al-sidebar adm-bg-dotgrid">
            <!-- Brand -->
            <div class="al-brand">
                <div class="al-brand-stamp">COY</div>
                <div>
                    <p class="al-brand-name">Chapter of You</p>
                    <p class="al-brand-sub">Admin Docket</p>
                </div>
            </div>

            <!-- Nav -->
            <nav class="al-nav">
                <template v-for="(group, gi) in navGroups" :key="group.label">
                    <p
                        class="al-group-label adm-stagger-item"
                        :style="{ '--i': gi * 5 }"
                    >
                        {{ group.label }}
                    </p>
                    <Link
                        v-for="(link, li) in group.links"
                        :key="link.route"
                        :href="route(link.route)"
                        class="al-link adm-stagger-item"
                        :style="{ '--i': gi * 5 + li + 1 }"
                        :class="{
                            'al-link--active': isRouteActive(link.route),
                        }"
                    >
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
                    <div class="al-avatar">
                        {{ firstName.charAt(0).toUpperCase() }}
                    </div>
                    <span class="al-user-name">{{ firstName }}</span>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="al-main">
            <!-- Topbar -->
            <header class="al-topbar">
                <button
                    @click="sidebarOpen = true"
                    class="al-hamburger"
                    aria-label="Open menu"
                >
                    <Menu :size="17" :stroke-width="2.25" />
                </button>

                <span class="al-topbar-mark"></span>
                <span class="al-topbar-page">{{ pageTitle }}</span>

                <div class="al-topbar-right">
                    <span class="al-topbar-greeting">Hi, {{ firstName }}</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="al-logout-btn"
                    >
                        <LogOut :size="13" :stroke-width="2.25" />
                        Sign out
                    </Link>
                </div>
            </header>

            <!-- Page -->
            <main class="al-content adm-bg-grain">
                <slot />
            </main>
        </div>

        <!-- Mobile drawer -->
        <Transition name="al-backdrop">
            <div
                v-if="sidebarOpen"
                class="al-drawer-bg"
                @click.self="sidebarOpen = false"
            >
                <Transition name="al-drawer">
                    <div v-if="sidebarOpen" class="al-drawer adm-bg-dotgrid">
                        <div class="al-drawer-head">
                            <div
                                class="al-brand"
                                style="
                                    padding: 0;
                                    border: none;
                                    background: none;
                                "
                            >
                                <div class="al-brand-stamp">COY</div>
                                <p
                                    class="al-brand-name"
                                    style="color: var(--adm-paper-raised)"
                                >
                                    Chapter of You
                                </p>
                            </div>
                            <button
                                @click="sidebarOpen = false"
                                class="al-close-btn"
                                aria-label="Close"
                            >
                                <X :size="15" :stroke-width="2.25" />
                            </button>
                        </div>

                        <nav class="al-nav">
                            <template
                                v-for="group in navGroups"
                                :key="group.label"
                            >
                                <p class="al-group-label">{{ group.label }}</p>
                                <Link
                                    v-for="link in group.links"
                                    :key="link.route"
                                    :href="route(link.route)"
                                    @click="sidebarOpen = false"
                                    class="al-link"
                                    :class="{
                                        'al-link--active': isRouteActive(
                                            link.route,
                                        ),
                                    }"
                                >
                                    <component
                                        :is="link.icon"
                                        class="al-link-icon"
                                    />
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
    display: flex;
    min-height: 100vh;
    background: var(--adm-paper);
    color: var(--adm-ink);
    font-family: var(--adm-font);
}

/* Sidebar */
.al-sidebar {
    width: 248px;
    flex-shrink: 0;
    display: none;
    flex-direction: column;
    position: fixed;
    inset-y: 0;
    left: 0;
    height: 100vh;
    overflow: hidden;
    z-index: 30;
    box-shadow: 1px 0 0 var(--adm-line-dark);
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
    gap: 0.85rem;
    border-bottom: 1px dashed var(--adm-line-dark);
    flex-shrink: 0;
}

.al-brand-stamp {
    width: 38px;
    height: 38px;
    border-radius: var(--adm-radius-sm);
    background: transparent;
    border: 1.5px dashed var(--adm-stamp);
    color: var(--adm-stamp);
    font-family: var(--adm-font);
    font-size: 0.66rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transform: rotate(-3deg);
}

.al-brand-name {
    font-family: var(--adm-display);
    font-style: italic;
    font-size: 1rem;
    font-weight: 400;
    color: var(--adm-paper-raised);
    line-height: 1.2;
}

.al-brand-sub {
    font-size: 0.63rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgba(250, 246, 236, 0.4);
    margin-top: 0.15rem;
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
    color: rgba(250, 246, 236, 0.35);
}

.al-link {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem 0.75rem;
    min-height: 40px;
    font-size: 0.8rem;
    font-weight: 500;
    color: rgba(250, 246, 236, 0.58);
    text-decoration: none;
    border-radius: var(--adm-radius-sm);
    border-left: 2px solid transparent;
    margin-bottom: 1px;
    transition:
        background 0.15s,
        color 0.15s,
        border-color 0.15s;
}

.al-link:hover {
    background: rgba(250, 246, 236, 0.06);
    color: rgba(250, 246, 236, 0.9);
}

.al-link--active {
    color: var(--adm-paper-raised);
    font-weight: 600;
    background: rgba(232, 84, 58, 0.14);
    border-left-color: var(--adm-stamp);
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
    border-top: 1px dashed var(--adm-line-dark);
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    flex-shrink: 0;
}

.al-ext-link {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.76rem;
    font-weight: 500;
    color: rgba(250, 246, 236, 0.38);
    text-decoration: none;
    transition: color 0.15s;
}

.al-ext-link:hover {
    color: rgba(250, 246, 236, 0.8);
}

.al-user-chip {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.55rem 0.75rem;
    background: rgba(250, 246, 236, 0.05);
    border-radius: var(--adm-radius);
}

.al-avatar {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--adm-stamp);
    color: var(--adm-paper-raised);
    font-size: 0.7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.al-user-name {
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(250, 246, 236, 0.8);
}

/* Main */
.al-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

@media (min-width: 1024px) {
    .al-main {
        margin-left: 248px;
    }
}

/* Topbar */
.al-topbar {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(237, 231, 218, 0.86);
    backdrop-filter: blur(14px);
    border-bottom: 1px dashed var(--adm-line);
    padding: 0.85rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.al-hamburger {
    width: 34px;
    height: 34px;
    border-radius: var(--adm-radius);
    border: 1px solid var(--adm-line);
    background: var(--adm-paper-raised);
    color: var(--adm-ink);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
    flex-shrink: 0;
}

.al-hamburger:hover {
    background: var(--adm-paper);
}

@media (min-width: 1024px) {
    .al-hamburger {
        display: none;
    }
}

.al-topbar-mark {
    width: 8px;
    height: 8px;
    border-radius: 1px;
    background: var(--adm-stamp);
    transform: rotate(45deg);
    flex-shrink: 0;
    display: none;
}

@media (min-width: 640px) {
    .al-topbar-mark {
        display: block;
    }
}

.al-topbar-page {
    font-family: var(--adm-display);
    font-style: italic;
    font-size: 1.15rem;
    color: var(--adm-ink);
    flex: 1;
}

.al-topbar-right {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-shrink: 0;
}

.al-topbar-greeting {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--adm-ink-dim);
    display: none;
}

@media (min-width: 640px) {
    .al-topbar-greeting {
        display: block;
    }
}

.al-logout-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: var(--adm-radius);
    border: 1px solid var(--adm-line);
    background: var(--adm-paper-raised);
    color: var(--adm-ink-dim);
    font-family: var(--adm-font);
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition:
        background 0.15s,
        color 0.15s,
        border-color 0.15s;
}

.al-logout-btn:hover {
    background: var(--adm-danger);
    border-color: var(--adm-danger);
    color: var(--adm-paper-raised);
}

/* Content */
.al-content {
    flex: 1;
    padding: 2rem 1.5rem;
}

@media (min-width: 768px) {
    .al-content {
        padding: 2rem 2.5rem;
    }
}

/* Mobile drawer */
.al-drawer-bg {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(27, 25, 22, 0.45);
    backdrop-filter: blur(4px);
}

.al-drawer {
    position: absolute;
    inset-y: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.al-drawer-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.1rem 1.15rem;
    border-bottom: 1px dashed var(--adm-line-dark);
    flex-shrink: 0;
}

.al-close-btn {
    width: 30px;
    height: 30px;
    border-radius: var(--adm-radius);
    border: 1px solid rgba(250, 246, 236, 0.18);
    background: transparent;
    color: rgba(250, 246, 236, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s;
}

.al-close-btn:hover {
    background: rgba(250, 246, 236, 0.08);
    color: var(--adm-paper-raised);
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
