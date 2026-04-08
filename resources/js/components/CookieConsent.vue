<script setup lang="ts">
import { ref, onMounted } from 'vue';

const STORAGE_KEY = 'coy_cookie_consent';

const visible = ref(false);
const accepted = ref(false);

onMounted(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) {
        // Small delay so it doesn't flash immediately on page load
        setTimeout(() => { visible.value = true; }, 800);
    } else {
        accepted.value = stored === 'accepted';
        if (accepted.value) loadAnalytics();
    }
});

function accept() {
    localStorage.setItem(STORAGE_KEY, 'accepted');
    accepted.value = true;
    visible.value = false;
    loadAnalytics();
}

function decline() {
    localStorage.setItem(STORAGE_KEY, 'declined');
    accepted.value = false;
    visible.value = false;
}

function loadAnalytics() {
    // Only inject GA script after consent — prevents PECR violation
    if (typeof window === 'undefined') return;
    if (document.getElementById('ga-script')) return; // already loaded

    const script = document.createElement('script');
    script.id = 'ga-script';
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=G-HKD85XSYN0';
    document.head.appendChild(script);

    script.onload = () => {
        (window as any).dataLayer = (window as any).dataLayer || [];
        function gtag(...args: any[]) { (window as any).dataLayer.push(args); }
        gtag('js', new Date());
        gtag('config', 'G-HKD85XSYN0');
    };
}
</script>

<template>
    <Transition name="cc-slide">
        <div v-if="visible" class="cc-banner" role="dialog" aria-label="Cookie consent" aria-live="polite">
            <div class="cc-inner">
                <div class="cc-text">
                    <p class="cc-title">We use cookies</p>
                    <p class="cc-body">
                        I like to use analytics cookies to understand how you use our site so I can
                        make it better. I won't set them without your permission.
                        Read our
                        <a href="/privacy" class="cc-link">Privacy Policy</a>.
                    </p>
                </div>
                <div class="cc-actions">
                    <button @click="decline" class="cc-btn cc-btn--decline">
                        Decline
                    </button>
                    <button @click="accept" class="cc-btn cc-btn--accept">
                        Accept cookies
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.cc-banner {
    position: fixed;
    bottom: 1.25rem;
    left: 1.25rem;
    right: 1.25rem;
    max-width: 560px;
    z-index: 9999;
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(45, 26, 26, 0.14);
    overflow: hidden;
}

/* Rose accent top border */
.cc-banner::before {
    content: '';
    display: block;
    height: 3px;
    background: linear-gradient(90deg, #c47078, #a85058);
}

.cc-inner {
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    flex-wrap: wrap;
    font-family: 'Nunito', sans-serif;
}

.cc-text {
    flex: 1;
    min-width: 200px;
}

.cc-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #2d1a1a;
    margin-bottom: 0.35rem;
}

.cc-body {
    font-size: 0.82rem;
    color: #6b4f4f;
    line-height: 1.6;
}

.cc-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.cc-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-shrink: 0;
    align-self: center;
}

.cc-btn {
    padding: 0.55rem 1.1rem;
    border-radius: 999px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    white-space: nowrap;
}

.cc-btn--decline {
    border: 1px solid #e5c9c7;
    background: transparent;
    color: #6b4f4f;
}

.cc-btn--decline:hover {
    background: #fdf4f3;
    border-color: #c9a4a4;
}

.cc-btn--accept {
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    box-shadow: 0 3px 10px rgba(168, 80, 88, 0.22);
}

.cc-btn--accept:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(168, 80, 88, 0.3);
}

/* Slide up animation */
.cc-slide-enter-active {
    transition: all 0.35s cubic-bezier(.34, 1.56, .64, 1);
}

.cc-slide-leave-active {
    transition: all 0.25s ease;
}

.cc-slide-enter-from {
    transform: translateY(20px);
    opacity: 0;
}

.cc-slide-leave-to {
    transform: translateY(16px);
    opacity: 0;
}

@media (max-width: 480px) {
    .cc-banner {
        left: 0.75rem;
        right: 0.75rem;
        bottom: 0.75rem;
    }

    .cc-inner {
        padding: 1rem;
    }

    .cc-actions {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
