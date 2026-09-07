<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="footer coy-storefront">
        <section class="footer-support" aria-label="Shopping reassurance">
            <div class="coy-container support-grid">
                <div class="support-item">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path
                            d="M3 6h13v11H3zM16 10h3l2 3v4h-5zM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM18 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"
                        />
                    </svg>
                    <div>
                        <strong>Free UK delivery</strong
                        ><span>On orders of £50 or more</span>
                    </div>
                </div>
                <div class="support-item">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path
                            d="M12 21s-7-4.5-7-11a4 4 0 0 1 7-2.7A4 4 0 0 1 19 10c0 6.5-7 11-7 11Z"
                        />
                    </svg>
                    <div>
                        <strong>Made with care</strong
                        ><span>Thoughtfully created in the UK</span>
                    </div>
                </div>
                <div class="support-item">
                    <svg aria-hidden="true" viewBox="0 0 24 24">
                        <path
                            d="M12 3 4 6v5c0 5 3.4 8.6 8 10 4.6-1.4 8-5 8-10V6l-8-3Z"
                        />
                        <path d="m9 12 2 2 4-4" />
                    </svg>
                    <div>
                        <strong>Shop with confidence</strong
                        ><span>Secure checkout and helpful support</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="footer-main">
            <div class="coy-container footer-grid">
                <div class="footer-brand">
                    <Link href="/" class="footer-logo">Chapter of You</Link>
                    <p class="footer-tagline">Self-care that feels personal.</p>
                    <p class="footer-intro">
                        Thoughtful products and rituals created to bring a
                        little more calm, confidence and joy to everyday life.
                    </p>
                    <div class="footer-cta">
                        <p>Not sure where to begin?</p>
                        <Link :href="route('scent-finder.index')"
                            >Find your scent
                            <span aria-hidden="true">→</span></Link
                        >
                    </div>
                </div>

                <nav class="footer-nav" aria-label="Footer navigation">
                    <section>
                        <h2>Explore</h2>
                        <ul>
                            <li>
                                <Link :href="route('products')"
                                    >All products</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('gift-vouchers.index')"
                                    >Gift vouchers</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('scent-finder.index')"
                                    >Scent finder</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('journal.index')"
                                    >Journal</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('about')">Our story</Link>
                            </li>
                        </ul>
                    </section>
                    <section>
                        <h2>Here to help</h2>
                        <ul>
                            <li>
                                <Link :href="route('contact')">Contact us</Link>
                            </li>
                            <li>
                                <Link :href="route('delivery')"
                                    >Delivery information</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('returns')"
                                    >Returns and refunds</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('cart.view')"
                                    >Your basket</Link
                                >
                            </li>
                        </ul>
                    </section>
                    <section>
                        <h2>Your account</h2>
                        <ul>
                            <li>
                                <Link :href="route('account.index')"
                                    >Account overview</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('account.orders.index')"
                                    >Your orders</Link
                                >
                            </li>
                            <li>
                                <Link :href="route('wishlist.index')"
                                    >Wishlist</Link
                                >
                            </li>
                            <li><Link :href="route('login')">Sign in</Link></li>
                        </ul>
                    </section>
                </nav>
            </div>

            <div class="coy-container footer-bottom">
                <p>
                    &copy; {{ currentYear }} Chapter of You. All rights
                    reserved.
                </p>
                <nav aria-label="Legal">
                    <Link :href="route('privacy')">Privacy</Link>
                    <Link :href="route('terms')">Terms</Link>
                    <Link :href="route('returns')">Returns</Link>
                </nav>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.footer {
    margin-top: auto;
    color: var(--coy-color-text);
    background: var(--coy-color-page);
}
.footer-support {
    background: var(--coy-color-champagne);
    border-top: 1px solid var(--coy-color-border);
}
.support-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
}
.support-item {
    min-height: 7.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.9rem;
    padding: 1.5rem;
}
.support-item + .support-item {
    border-left: 1px solid rgb(102 90 87/20%);
}
.support-item svg {
    width: 1.8rem;
    flex: 0 0 auto;
    fill: none;
    stroke: var(--coy-color-accent);
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.support-item strong,
.support-item span {
    display: block;
}
.support-item strong {
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.2;
}
.support-item span {
    margin-top: 0.25rem;
    font-size: 1rem;
    line-height: 1.35;
}
.footer-main {
    color: #eadfda;
    background: var(--coy-color-heading);
}
.footer-grid {
    display: grid;
    grid-template-columns: minmax(17rem, 1.1fr) minmax(0, 2fr);
    gap: clamp(3rem, 7vw, 7rem);
    padding-block: clamp(3.5rem, 7vw, 6rem);
}
.footer-logo {
    color: var(--coy-color-white);
    font-family: var(--coy-font-display);
    font-size: clamp(2rem, 3vw, 2.75rem);
    font-weight: 500;
    line-height: 1;
    text-decoration: none;
}
.footer-tagline {
    margin: 0.75rem 0 0;
    color: var(--coy-color-rose);
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.footer-intro {
    max-width: 31rem;
    margin: 1.25rem 0 0;
    font-size: 1.0625rem;
    line-height: 1.65;
}
.footer-cta {
    margin-top: 2rem;
    padding: 1.1rem 1.25rem;
    background: rgb(255 253 251/7%);
    border: 1px solid rgb(235 199 207/24%);
    border-radius: var(--coy-radius-md);
}
.footer-cta p {
    margin: 0;
    font-size: 1rem;
}
.footer-cta a {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-top: 0.35rem;
    color: var(--coy-color-white);
    font-size: 1.0625rem;
    font-weight: 700;
    text-underline-offset: 0.3rem;
}
.footer-nav {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: clamp(1.75rem, 4vw, 4rem);
}
.footer-nav h2 {
    margin: 0;
    padding-bottom: 0.8rem;
    color: var(--coy-color-white);
    border-bottom: 1px solid rgb(235 199 207/24%);
    font-family: var(--coy-font-display);
    font-size: 1.35rem;
    font-weight: 600;
}
.footer-nav ul {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
    margin: 1rem 0 0;
    padding: 0;
    list-style: none;
}
.footer-nav a,
.footer-bottom a {
    color: #eadfda;
    font-size: 1rem;
    text-decoration-color: transparent;
    text-underline-offset: 0.3rem;
    transition:
        color var(--coy-duration-fast),
        text-decoration-color var(--coy-duration-fast);
}
.footer-nav a:hover,
.footer-bottom a:hover {
    color: var(--coy-color-white);
    text-decoration-color: currentColor;
}
.footer-bottom {
    min-height: 5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding-block: 1.25rem;
    border-top: 1px solid rgb(235 199 207/18%);
}
.footer-bottom p {
    margin: 0;
    font-size: 1rem;
}
.footer-bottom nav {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}
@media (max-width: 850px) {
    .footer-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 700px) {
    .support-grid {
        grid-template-columns: 1fr;
        padding-block: 0.5rem;
    }
    .support-item {
        min-height: 0;
        justify-content: flex-start;
        padding: 1.1rem 0;
    }
    .support-item + .support-item {
        border-top: 1px solid rgb(102 90 87/20%);
        border-left: 0;
    }
    .footer-nav {
        grid-template-columns: 1fr 1fr;
    }
    .footer-bottom {
        align-items: flex-start;
        flex-direction: column;
    }
}
@media (max-width: 480px) {
    .footer-grid {
        gap: 2.75rem;
    }
    .footer-nav {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .footer-bottom nav {
        gap: 1.25rem;
    }
}
</style>
