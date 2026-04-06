<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

// ── Newsletter / waitlist signup ──────────────────────────────────────────
const email = ref('');
const submitting = ref(false);
const submitted = ref(false);
const submitError = ref('');

async function submitNewsletter() {
    if (!email.value.trim() || submitting.value) return;
    submitting.value = true;
    submitError.value = '';
    try {
        await axios.post(route('waitlist.store'), { email: email.value.trim() });
        submitted.value = true;
        email.value = '';
    } catch (err: any) {
        const msg = err?.response?.data?.message
            ?? err?.response?.data?.errors?.email?.[0]
            ?? 'Something went wrong. Please try again.';
        submitError.value = msg;
    } finally {
        submitting.value = false;
    }
}

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="ft">

        <!-- ── Top wave / petal divider ── -->
        <div class="ft-crown" aria-hidden="true">
            <span class="ft-crown-petal">✿</span>
            <span class="ft-crown-line"></span>
            <span class="ft-crown-petal ft-crown-petal--sm">✿</span>
            <span class="ft-crown-line"></span>
            <span class="ft-crown-petal">✿</span>
        </div>

        <div class="ft-inner">

            <!-- ── Brand column ── -->
            <div class="ft-brand">
                <p class="ft-brand-name">Chapter of You</p>
                <p class="ft-brand-tagline">Your chapter, your self-care.</p>
                <p class="ft-brand-desc">
                    Luxurious hand-crafted reed diffusers, made with love and care for
                    your home and well-being.
                </p>

                <!-- Newsletter signup -->
                <div class="ft-newsletter">
                    <p class="ft-newsletter-label">Stay in the loop</p>
                    <div v-if="!submitted" class="ft-newsletter-form">
                        <input v-model="email" type="email" placeholder="your@email.co.uk" class="ft-newsletter-input"
                            :disabled="submitting" @keyup.enter="submitNewsletter"
                            aria-label="Email address for newsletter" />
                        <button @click="submitNewsletter" :disabled="submitting || !email.trim()"
                            class="ft-newsletter-btn" aria-label="Subscribe">
                            <svg v-if="submitting" class="ft-spinner" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                                <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3"
                                    stroke-linecap="round" />
                            </svg>
                            <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="submitError" class="ft-newsletter-error">{{ submitError }}</p>
                    <div v-if="submitted" class="ft-newsletter-success">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                        Thank you — you're on the list! ✦
                    </div>
                    <p class="ft-newsletter-note">No spam, ever. Unsubscribe at any time.</p>
                </div>
            </div>

            <!-- ── Links columns ── -->
            <nav class="ft-nav" aria-label="Footer navigation">

                <div class="ft-nav-col">
                    <p class="ft-nav-heading">Shop</p>
                    <ul class="ft-nav-list">
                        <li>
                            <Link :href="route('products')" class="ft-nav-link">All Products</Link>
                        </li>
                        <li>
                            <Link :href="route('cart.view')" class="ft-nav-link">My Basket</Link>
                        </li>
                        <li>
                            <Link :href="route('wishlist.index')" class="ft-nav-link">Wishlist</Link>
                        </li>
                    </ul>
                </div>

                <div class="ft-nav-col">
                    <p class="ft-nav-heading">Account</p>
                    <ul class="ft-nav-list">
                        <li>
                            <Link :href="route('account.index')" class="ft-nav-link">My Account</Link>
                        </li>
                        <li>
                            <Link :href="route('account.orders.index')" class="ft-nav-link">My Orders</Link>
                        </li>
                        <li>
                            <Link :href="route('login')" class="ft-nav-link">Sign In</Link>
                        </li>
                        <li>
                            <Link :href="route('register')" class="ft-nav-link">Register</Link>
                        </li>
                    </ul>
                </div>

                <div class="ft-nav-col">
                    <p class="ft-nav-heading">Information</p>
                    <ul class="ft-nav-list">
                        <li>
                            <Link :href="route('delivery')" class="ft-nav-link">Delivery Information</Link>
                        </li>
                        <li>
                            <Link :href="route('returns')" class="ft-nav-link">Returns &amp; Refunds</Link>
                        </li>
                        <li>
                            <Link :href="route('terms')" class="ft-nav-link">Terms &amp; Conditions</Link>
                        </li>
                        <li>
                            <Link :href="route('privacy')" class="ft-nav-link">Privacy Policy</Link>
                        </li>
                        <li>
                            <Link :href="route('contact')" class="ft-nav-link">Contact Us</Link>
                        </li>
                    </ul>
                </div>

            </nav>
        </div>

        <!-- ── Bottom bar ── -->
        <div class="ft-bottom">
            <div class="ft-bottom-inner">
                <p class="ft-copyright">
                    &copy; {{ currentYear }} Chapter of You. All rights reserved.
                </p>
                <div class="ft-bottom-links">
                    <Link :href="route('privacy')" class="ft-bottom-link">Privacy</Link>
                    <span class="ft-bottom-sep" aria-hidden="true">·</span>
                    <Link :href="route('terms')" class="ft-bottom-link">Terms</Link>
                    <span class="ft-bottom-sep" aria-hidden="true">·</span>
                    <Link :href="route('returns')" class="ft-bottom-link">Returns</Link>
                </div>
            </div>
        </div>

    </footer>
</template>

<style scoped>
.ft {
    font-family: 'Nunito', sans-serif;
    background: #2d1a1a;
    color: #e5c9c7;
    margin-top: auto;
}

/* ── Crown divider ── */
.ft-crown {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1.25rem 1.25rem 0;
}

.ft-crown-line {
    display: block;
    flex: 1;
    max-width: 120px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #6b4040, transparent);
}

.ft-crown-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
    opacity: 0.7;
}

.ft-crown-petal--sm {
    font-size: 0.55rem;
    opacity: 0.45;
}

/* ── Main inner ── */
.ft-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem 3rem;
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
    gap: 3rem;
    align-items: start;
}

@media (max-width: 900px) {
    .ft-inner {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

@media (max-width: 560px) {
    .ft-inner {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 2rem 1.25rem 2.5rem;
    }
}

/* ── Brand column ── */
.ft-brand-name {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.5rem;
    font-style: italic;
    font-weight: 400;
    color: #fffafa;
    margin-bottom: 0.35rem;
    letter-spacing: 0.02em;
}

.ft-brand-tagline {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.9rem;
}

.ft-brand-desc {
    font-size: 0.85rem;
    color: #9a7070;
    line-height: 1.7;
    max-width: 280px;
    margin-bottom: 1.5rem;
}

/* ── Newsletter ── */
.ft-newsletter-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.6rem;
}

.ft-newsletter-form {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid #6b4040;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(255, 250, 250, 0.05);
    transition: border-color 0.2s;
}

.ft-newsletter-form:focus-within {
    border-color: #c9a4a4;
}

.ft-newsletter-input {
    flex: 1;
    padding: 0.6rem 1rem;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    color: #fffafa;
    min-width: 0;
}

.ft-newsletter-input::placeholder {
    color: #6b4040;
}

.ft-newsletter-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.ft-newsletter-btn {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border: none;
    border-radius: 0 999px 999px 0;
    background: #8c4a50;
    color: #fffafa;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
}

.ft-newsletter-btn:hover:not(:disabled) {
    background: #a85058;
}

.ft-newsletter-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.ft-newsletter-error {
    font-size: 0.75rem;
    color: #e08080;
    margin-top: 0.4rem;
}

.ft-newsletter-success {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.82rem;
    color: #a8d8a8;
    font-weight: 600;
    padding: 0.5rem 0;
}

.ft-newsletter-note {
    font-size: 0.7rem;
    color: #5a3838;
    margin-top: 0.4rem;
}

/* ── Nav columns ── */
.ft-nav {
    display: contents;
    /* Let grid handle the columns */
}

.ft-nav-col {}

.ft-nav-heading {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 1rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid #3d2424;
}

.ft-nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
}

.ft-nav-link {
    font-size: 0.88rem;
    color: #9a7070;
    text-decoration: none;
    transition: color 0.15s;
    display: inline-block;
}

.ft-nav-link:hover {
    color: #e5c9c7;
}

/* ── Bottom bar ── */
.ft-bottom {
    border-top: 1px solid #3d2424;
}

.ft-bottom-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.ft-copyright {
    font-size: 0.75rem;
    color: #5a3838;
}

.ft-bottom-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ft-bottom-link {
    font-size: 0.75rem;
    color: #5a3838;
    text-decoration: none;
    transition: color 0.15s;
}

.ft-bottom-link:hover {
    color: #9a7070;
}

.ft-bottom-sep {
    color: #3d2424;
    font-size: 0.75rem;
}

/* ── Spinner ── */
.ft-spinner {
    width: 14px;
    height: 14px;
    animation: ft-spin 0.8s linear infinite;
    flex-shrink: 0;
}

@keyframes ft-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
