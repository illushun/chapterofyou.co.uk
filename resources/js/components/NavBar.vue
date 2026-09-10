<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import CookieConsent from '@/components/CookieConsent.vue';
import NavSearch from '@/components/NavSearch.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

declare const route: (name: string) => string;

const page = usePage();
const isAccountOpen = ref(false);
const isMobileMenuOpen = ref(false);
const cartCount = computed(() => Number((page.props as any).cartCount) || 0);
const firstName = computed(() => {
    const name = (page.props as any).auth?.user?.name;
    return name ? name.split(' ')[0] : 'Account';
});
</script>

<template>
    <header class="site-header coy-storefront">
        <div class="primary-row coy-container">
            <a href="/" class="brand" aria-label="Chapter of You home">
                <AppLogoIcon class-name="brand-mark" aria-hidden="true" />
            </a>

            <NavSearch class="search--desktop" />

            <nav class="actions" aria-label="Account and basket">
                <div class="account">
                    <button
                        v-if="$page.props.auth.user"
                        type="button"
                        class="action"
                        :aria-expanded="isAccountOpen"
                        aria-haspopup="true"
                        @click="isAccountOpen = !isAccountOpen"
                    >
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 21a8 8 0 0 1 16 0" />
                        </svg>
                        <span>{{ firstName }}</span>
                    </button>
                    <a v-else href="/login" class="action">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 21a8 8 0 0 1 16 0" />
                        </svg>
                        <span>Sign in</span>
                    </a>
                    <Transition name="dropdown">
                        <div
                            v-if="isAccountOpen && $page.props.auth.user"
                            class="account-menu"
                            @click="isAccountOpen = false"
                            v-click-outside="() => (isAccountOpen = false)"
                        >
                            <a href="/account">My account</a
                            ><a href="/account/orders">My orders</a
                            ><a href="/account/wishlist">Wishlist</a>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                type="button"
                                >Log out</Link
                            >
                        </div>
                    </Transition>
                </div>
                <a href="/cart" class="action basket" aria-label="Basket">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path d="M3 4h2l2.2 10h10.6L21 7H6" />
                        <circle cx="9" cy="19" r="1.5" />
                        <circle cx="18" cy="19" r="1.5" />
                    </svg>
                    <span>Basket</span
                    ><b v-if="cartCount">{{
                        cartCount > 99 ? '99+' : cartCount
                    }}</b>
                </a>
                <button
                    type="button"
                    class="menu-button"
                    :aria-expanded="isMobileMenuOpen"
                    aria-controls="mobile-navigation"
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                >
                    <svg
                        v-if="!isMobileMenuOpen"
                        aria-hidden="true"
                        viewBox="0 0 24 24"
                    >
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else aria-hidden="true" viewBox="0 0 24 24">
                        <path d="m6 6 12 12M18 6 6 18" />
                    </svg>
                    <span class="sr-only">{{
                        isMobileMenuOpen ? 'Close menu' : 'Open menu'
                    }}</span>
                </button>
            </nav>
        </div>

        <NavSearch
            compact
            class="search--mobile"
            @submitted="isMobileMenuOpen = false"
        />

        <nav class="category-row" aria-label="Main navigation">
            <div class="coy-container">
                <a href="/products">Products</a
                ><a href="/scent-finder">Scent Finder</a
                ><a href="/gift-vouchers">Gifts</a><a href="/about">Our Story</a
                ><a href="/journal">Journal</a><a href="/contact">Contact</a>
            </div>
        </nav>

        <Transition name="mobile-menu">
            <nav
                v-if="isMobileMenuOpen"
                id="mobile-navigation"
                class="mobile-menu"
                aria-label="Mobile navigation"
            >
                <a href="/products">Products <span>→</span></a
                ><a href="/scent-finder">Find my scent <span>→</span></a
                ><a href="/gift-vouchers">Gift vouchers <span>→</span></a
                ><a href="/about">Our Story <span>→</span></a
                ><a href="/journal">Journal <span>→</span></a
                ><a href="/contact">Contact <span>→</span></a>
                <a
                    v-if="$page.props.auth.user"
                    href="/account"
                    class="mobile-account"
                    >My account</a
                ><a v-else href="/login" class="mobile-account"
                    >Sign in or create an account</a
                >
            </nav>
        </Transition>
    </header>
    <CookieConsent />
</template>

<style scoped>
.site-header {
    position: fixed;
    inset: 0 0 auto;
    z-index: 30;
    height: var(--coy-nav-height);
    background: var(--coy-color-surface);
    border-bottom: 1px solid var(--coy-color-border);
    box-shadow: var(--coy-shadow-sm);
}
.primary-row {
    height: 4.5rem;
    display: grid;
    grid-template-columns: auto minmax(18rem, 42rem) auto;
    align-items: center;
    justify-content: space-between;
    gap: clamp(1.25rem, 3vw, 3rem);
}
.brand {
    display: flex;
    align-items: center;
    color: var(--coy-color-heading);
    text-decoration: none;
    flex-shrink: 0;
}
.brand-mark {
    width: 3.25rem;
    height: 3.25rem;
}
.actions {
    display: flex;
    align-items: center;
    justify-self: end;
    gap: 0.5rem;
    margin-left: auto;
}
.account {
    position: relative;
}
.action,
.menu-button {
    min-height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.4rem 0.65rem;
    color: var(--coy-color-heading);
    background: transparent;
    border: 0;
    border-radius: var(--coy-radius-sm);
    font: inherit;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}
.action:hover,
.menu-button:hover {
    background: var(--coy-color-page);
}
.action svg,
.menu-button svg {
    width: 1.35rem;
    height: 1.35rem;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.basket {
    position: relative;
}
.basket b {
    min-width: 1.35rem;
    height: 1.35rem;
    display: grid;
    place-items: center;
    padding: 0 0.25rem;
    color: white;
    background: var(--coy-color-accent);
    border-radius: 999px;
    font-size: 0.875rem;
}
.menu-button {
    display: none;
}
.account-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    width: 12rem;
    padding: 0.5rem;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-md);
    box-shadow: var(--coy-shadow-md);
}
.account-menu a,
.account-menu button {
    width: 100%;
    display: block;
    padding: 0.65rem 0.75rem;
    color: var(--coy-color-heading);
    background: transparent;
    border: 0;
    border-radius: var(--coy-radius-sm);
    font: inherit;
    font-size: 1rem;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
}
.account-menu a:hover,
.account-menu button:hover {
    background: var(--coy-color-page);
}
.account-menu button {
    margin-top: 0.25rem;
    padding-top: 0.75rem;
    color: var(--coy-color-error);
    border-top: 1px solid var(--coy-color-border-soft);
    border-radius: 0;
}
.category-row {
    height: 2.75rem;
    border-top: 1px solid var(--coy-color-border-soft);
}
.category-row > div {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: clamp(1.5rem, 4vw, 3.5rem);
}
.category-row a {
    color: var(--coy-color-heading);
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
}
.category-row a:hover {
    color: var(--coy-color-accent);
}
.search--mobile,
.mobile-menu {
    display: none;
}
.dropdown-enter-active,
.dropdown-leave-active,
.mobile-menu-enter-active,
.mobile-menu-leave-active {
    transition:
        opacity 0.18s,
        transform 0.18s;
}
.dropdown-enter-from,
.dropdown-leave-to,
.mobile-menu-enter-from,
.mobile-menu-leave-to {
    opacity: 0;
    transform: translateY(-0.4rem);
}
@media (max-width: 900px) {
    .primary-row {
        display: flex;
        justify-content: space-between;
    }
    .search--desktop {
        display: none;
    }
    .menu-button {
        display: flex;
    }
    .category-row {
        display: none;
    }
    .search--mobile {
        display: flex;
        margin: 0 var(--coy-gutter);
    }
    .site-header {
        padding-bottom: 0.75rem;
    }
    .mobile-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        display: flex;
        flex-direction: column;
        padding: 1rem var(--coy-gutter) 1.25rem;
        background: var(--coy-color-surface);
        border-top: 1px solid var(--coy-color-border);
        box-shadow: var(--coy-shadow-md);
    }
    .mobile-menu a {
        display: flex;
        justify-content: space-between;
        padding: 0.8rem 0.25rem;
        color: var(--coy-color-heading);
        border-bottom: 1px solid var(--coy-color-border-soft);
        font-size: 1.0625rem;
        font-weight: 600;
        text-decoration: none;
    }
    .mobile-menu .mobile-account {
        margin-top: 0.75rem;
        justify-content: center;
        color: var(--coy-color-on-accent);
        background: var(--coy-color-accent);
        border: 0;
        border-radius: var(--coy-radius-pill);
    }
}
@media (max-width: 540px) {
    .primary-row {
        height: 4.25rem;
    }
    .brand-mark {
        width: 2.75rem;
        height: 2.75rem;
    }
    .action {
        padding: 0.4rem;
    }
    .action > span {
        display: none;
    }
    .actions {
        gap: 0.1rem;
    }
    .search--mobile {
        height: 2.75rem;
    }
}
</style>
