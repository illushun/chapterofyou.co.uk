<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

const seo = useSeoHead({
    title: 'Privacy Policy',
    description: 'Privacy policy for Chapter of You. Learn how we collect, use, and protect your personal information.',
    canonical: '/privacy',
    noIndex: false, // Privacy pages should be indexed — it builds trust
});

const lastUpdated = '6 April 2026';
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="pp">
        <div class="pp-wrap">

            <!-- ── Header ── -->
            <header class="pp-header">
                <p class="pp-eyebrow">Chapter of You</p>
                <h1 class="pp-title">Privacy Policy</h1>
                <div class="pp-title-rule">
                    <span class="pp-rule-line"></span>
                    <span class="pp-rule-petal">✿</span>
                    <span class="pp-rule-line"></span>
                </div>
                <p class="pp-intro">
                    Your privacy is important to me. This policy explains how your personal
                    information is collected, used, and protected when you visit or make a
                    purchase from this website.
                </p>
                <p class="pp-updated">Last updated: {{ lastUpdated }}</p>
            </header>

            <!-- ── Quick nav ── -->
            <nav class="pp-nav" aria-label="Jump to section">
                <p class="pp-nav-label">Jump to</p>
                <div class="pp-nav-links">
                    <a href="#information-collected" class="pp-nav-link">What I Collect</a>
                    <a href="#how-used" class="pp-nav-link">How It's Used</a>
                    <a href="#sharing" class="pp-nav-link">Sharing</a>
                    <a href="#data-protection" class="pp-nav-link">Data Protection</a>
                    <a href="#cookies" class="pp-nav-link">Cookies</a>
                    <a href="#your-rights" class="pp-nav-link">Your Rights</a>
                    <a href="#contact" class="pp-nav-link">Contact</a>
                </div>
            </nav>

            <!-- ── Body ── -->
            <div class="pp-body">

                <!-- What I collect -->
                <section id="information-collected" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">01</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">Information I Collect</h2>
                        <div class="pp-section-body">
                            <p>
                                When you place an order or create an account, I may collect the
                                following personal information:
                            </p>
                            <ul class="pp-list">
                                <li>Your name</li>
                                <li>Billing and shipping address</li>
                                <li>Email address</li>
                                <li>Telephone number (optional)</li>
                                <li>Payment details — processed securely via Stripe and never stored by this website
                                </li>
                            </ul>
                            <p>
                                I may also collect non-personal browsing data such as pages visited and
                                time spent on the site, via Google Analytics, to help improve the
                                customer experience.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="pp-divider" aria-hidden="true">✿</div>

                <!-- How it's used -->
                <section id="how-used" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">02</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">How Your Information Is Used</h2>
                        <div class="pp-section-body">
                            <p>Your personal information is used solely to:</p>
                            <ul class="pp-list">
                                <li>Process and fulfil your order</li>
                                <li>Communicate with you about your order status and delivery</li>
                                <li>Provide customer support</li>
                                <li>Send you marketing emails, <strong>only if you have opted in</strong></li>
                                <li>Improve the website and customer experience</li>
                            </ul>
                            <p>
                                I will never use your information for any purpose beyond what is
                                described above without your explicit consent.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="pp-divider" aria-hidden="true">✿</div>

                <!-- Sharing -->
                <section id="sharing" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">03</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">Sharing Your Information</h2>
                        <div class="pp-section-body">
                            <p>
                                Your personal information may be shared with trusted third parties,
                                but only where strictly necessary to complete your order:
                            </p>
                            <div class="pp-third-parties">
                                <div class="pp-third-party">
                                    <div class="pp-tp-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="1" y="4" width="22" height="16" rx="2" />
                                            <line x1="1" y1="10" x2="23" y2="10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="pp-tp-name">Stripe</p>
                                        <p class="pp-tp-desc">Payment processing — your card details are handled
                                            entirely by Stripe and are never seen or stored by this website.</p>
                                    </div>
                                </div>
                                <div class="pp-third-party">
                                    <div class="pp-tp-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect x="1" y="3" width="15" height="13" rx="1" />
                                            <path d="M16 8h4l3 3v5h-7V8z" />
                                            <circle cx="5.5" cy="18.5" r="2.5" />
                                            <circle cx="18.5" cy="18.5" r="2.5" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="pp-tp-name">Couriers</p>
                                        <p class="pp-tp-desc">Your name and delivery address are shared with the courier
                                            service used to fulfil your order (e.g. Royal Mail).</p>
                                    </div>
                                </div>
                                <div class="pp-third-party">
                                    <div class="pp-tp-icon">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="pp-tp-name">Google Analytics</p>
                                        <p class="pp-tp-desc">Anonymous usage data is collected to help improve the
                                            website. No personally identifiable information is shared.</p>
                                    </div>
                                </div>
                            </div>
                            <p>
                                Your information will <strong>never be sold</strong> to third parties or
                                shared for marketing purposes without your explicit consent.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="pp-divider" aria-hidden="true">✿</div>

                <!-- Data protection -->
                <section id="data-protection" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">04</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">Data Protection</h2>
                        <div class="pp-section-body">
                            <p>
                                Your personal information is handled securely. This website uses
                                industry-standard encryption (HTTPS/TLS) for all data transmitted between
                                your browser and the server.
                            </p>
                            <p>
                                Payment information is processed securely by Stripe and is never
                                stored on this website's servers.
                            </p>
                            <p>
                                I retain your order information for as long as is necessary to fulfil
                                legal and accounting obligations. You may request deletion of your
                                personal data at any time (see Your Rights below).
                            </p>
                        </div>
                    </div>
                </section>

                <div class="pp-divider" aria-hidden="true">✿</div>

                <!-- Cookies -->
                <section id="cookies" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">05</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">Cookies</h2>
                        <div class="pp-section-body">
                            <p>
                                This website uses a small number of cookies that are necessary for
                                the site to function correctly:
                            </p>
                            <ul class="pp-list">
                                <li><strong>Session cookie</strong> — keeps you logged in and maintains your shopping
                                    cart.</li>
                                <li><strong>CSRF token</strong> — protects against cross-site request forgery attacks.
                                </li>
                                <li><strong>Google Analytics cookies</strong> — anonymous usage data to help improve the
                                    site. You can opt out via <a href="https://tools.google.com/dlpage/gaoptout"
                                        target="_blank" rel="noopener noreferrer" class="pp-ext-link">Google's opt-out
                                        tool</a>.</li>
                            </ul>
                            <p>
                                No advertising or tracking cookies from third-party marketing platforms
                                are used on this website.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="pp-divider" aria-hidden="true">✿</div>

                <!-- Your rights -->
                <section id="your-rights" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">06</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">Your Rights</h2>
                        <div class="pp-section-body">
                            <p>
                                Under UK data protection law (UK GDPR), you have the following rights
                                regarding your personal data:
                            </p>
                            <ul class="pp-list">
                                <li><strong>Right to access</strong> — request a copy of the personal data I hold about
                                    you.</li>
                                <li><strong>Right to rectification</strong> — request correction of inaccurate or
                                    incomplete data.</li>
                                <li><strong>Right to erasure</strong> — request deletion of your personal data, subject
                                    to legal obligations.</li>
                                <li><strong>Right to restrict processing</strong> — request that I limit how your data
                                    is used.</li>
                                <li><strong>Right to object</strong> — object to your data being used for marketing
                                    purposes at any time.</li>
                                <li><strong>Right to data portability</strong> — request a copy of your data in a
                                    commonly used format.</li>
                            </ul>
                            <div class="pp-callout">
                                <div class="pp-callout-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.08 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z" />
                                    </svg>
                                </div>
                                <p>
                                    To exercise any of these rights, contact me at
                                    <a href="mailto:contact@chapterofyou.co.uk"
                                        class="pp-email-link">contact@chapterofyou.co.uk</a>.
                                    I will respond within <strong>30 days</strong>.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="pp-divider" aria-hidden="true">✿</div>

                <!-- Contact -->
                <section id="contact" class="pp-section">
                    <div class="pp-section-num" aria-hidden="true">07</div>
                    <div class="pp-section-content">
                        <h2 class="pp-section-title">Contact &amp; Complaints</h2>
                        <div class="pp-section-body">
                            <p>
                                If you have any questions about this privacy policy or how your data
                                is handled, please get in touch:
                            </p>
                            <p>
                                <a href="mailto:contact@chapterofyou.co.uk" class="pp-email-link pp-email-link--lg">
                                    contact@chapterofyou.co.uk
                                </a>
                            </p>
                            <p>
                                If you are unhappy with how your data has been handled, you have the
                                right to lodge a complaint with the
                                <a href="https://ico.org.uk/make-a-complaint/" target="_blank" rel="noopener noreferrer"
                                    class="pp-ext-link">
                                    Information Commissioner's Office (ICO)
                                </a>
                                — the UK's independent data protection authority.
                            </p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ── Footer strip ── -->
            <div class="pp-footer-strip">
                <p class="pp-footer-petal" aria-hidden="true">✿</p>
                <p class="pp-footer-text">Privacy questions?</p>
                <a href="mailto:contact@chapterofyou.co.uk" class="pp-footer-link">
                    contact@chapterofyou.co.uk
                </a>
            </div>

        </div>
    </main>

    <Footer />
</template>

<style scoped>
.pp {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.pp-wrap {
    max-width: 760px;
    margin: 0 auto;
    padding: 4rem 1.25rem 6rem;
}

/* ── Header ── */
.pp-header {
    text-align: center;
    margin-bottom: 3rem;
}

.pp-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.pp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 6vw, 3rem);
    font-weight: 400;
    font-style: italic;
    color: #2d1a1a;
    line-height: 1.1;
    margin-bottom: 1.25rem;
}

.pp-title-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.pp-rule-line {
    display: block;
    width: 60px;
    height: 1px;
    background: #e5c9c7;
}

.pp-rule-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
}

.pp-intro {
    font-size: 0.97rem;
    color: #6b4f4f;
    line-height: 1.7;
    max-width: 520px;
    margin: 0 auto 0.75rem;
}

.pp-updated {
    font-size: 0.75rem;
    color: #c9a4a4;
    font-style: italic;
}

/* ── Jump nav ── */
.pp-nav {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 2.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.pp-nav-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #c9a4a4;
    flex-shrink: 0;
}

.pp-nav-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.pp-nav-link {
    font-size: 0.78rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fdf4f3;
    transition: background 0.15s, border-color 0.15s;
}

.pp-nav-link:hover {
    background: #faeaea;
    border-color: #c9a4a4;
}

/* ── Body card ── */
.pp-body {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    overflow: hidden;
    margin-bottom: 2rem;
}

/* ── Sections ── */
.pp-section {
    display: flex;
    gap: 1.5rem;
    padding: 2rem;
    align-items: flex-start;
    scroll-margin-top: 88px;
}

@media (max-width: 540px) {
    .pp-section {
        flex-direction: column;
        gap: 0.5rem;
        padding: 1.5rem;
    }
}

.pp-section-num {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 400;
    font-style: italic;
    color: #e5c9c7;
    line-height: 1;
    flex-shrink: 0;
    width: 42px;
    text-align: right;
    padding-top: 0.1rem;
}

@media (max-width: 540px) {
    .pp-section-num {
        width: auto;
        text-align: left;
    }
}

.pp-section-content {
    flex: 1;
    min-width: 0;
}

.pp-section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.35rem;
    font-weight: 500;
    color: #2d1a1a;
    margin-bottom: 0.85rem;
    line-height: 1.2;
}

.pp-section-body {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.pp-section-body p {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.75;
}

.pp-section-body strong {
    color: #2d1a1a;
    font-weight: 600;
}

/* List */
.pp-list {
    padding-left: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.pp-list li {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.65;
}

.pp-list li strong {
    color: #2d1a1a;
}

/* Third-party cards */
.pp-third-parties {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.pp-third-party {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    background: #fdf4f3;
    border: 1px solid #e5c9c7;
}

.pp-tp-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(140, 74, 80, 0.07);
    border: 1px solid #e5c9c7;
    color: #8c4a50;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.pp-tp-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #2d1a1a;
    margin-bottom: 0.15rem;
}

.pp-tp-desc {
    font-size: 0.82rem;
    color: #6b4f4f;
    line-height: 1.55;
}

/* Callout box */
.pp-callout {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.9rem 1rem;
    background: rgba(140, 74, 80, 0.05);
    border: 1px solid #e5c9c7;
    border-left: 3px solid #8c4a50;
    border-radius: 0 10px 10px 0;
}

.pp-callout-icon {
    color: #8c4a50;
    flex-shrink: 0;
    margin-top: 1px;
}

.pp-callout p {
    font-size: 0.88rem;
    color: #6b4f4f;
    line-height: 1.65;
    margin: 0;
}

/* Links */
.pp-email-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
}

.pp-email-link:hover {
    text-decoration: underline;
}

.pp-email-link--lg {
    font-size: 0.95rem;
}

.pp-ext-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.pp-ext-link:hover {
    text-decoration: none;
}

/* Divider */
.pp-divider {
    text-align: center;
    color: #e5c9c7;
    font-size: 0.7rem;
    letter-spacing: 0.4rem;
    padding: 0.15rem 0;
    border-top: 1px solid #f0dcd8;
}

/* ── Footer strip ── */
.pp-footer-strip {
    text-align: center;
    padding: 2rem 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.pp-footer-petal {
    font-size: 1.2rem;
    color: #c9a4a4;
}

.pp-footer-text {
    font-size: 0.9rem;
    color: #6b4f4f;
}

.pp-footer-link {
    font-size: 0.9rem;
    font-weight: 600;
    color: #8c4a50;
    text-decoration: none;
}

.pp-footer-link:hover {
    text-decoration: underline;
}
</style>
