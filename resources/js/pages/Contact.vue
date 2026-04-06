<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';

const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        onSuccess: () => {
            successToastRef.value?.show('Message Sent!', 'check');
            form.reset('name', 'email', 'subject', 'message');
        },
        onError: (errors) => console.error('Submission failed:', errors),
        preserveScroll: true,
    });
};

const seo = useSeoHead({
    title: 'Contact Us',
    description: 'Get in touch with Chapter of You. I\'d love to hear from you and will respond within 1–2 working days.',
    canonical: '/contact',
});
</script>

<template>
    <NavBar />

    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="cp">
        <div class="cp-wrap">

            <!-- Header -->
            <header class="cp-header">
                <h1 class="cp-title">Get in Touch</h1>
                <p class="cp-sub">
                    I'd love to hear from you. Fill out the form below and I'll get back to you as soon as possible.
                </p>
            </header>

            <!-- Two-column layout: form + info -->
            <div class="cp-grid">

                <!-- Contact form -->
                <form @submit.prevent="submit" class="cp-card">

                    <div class="field-row">
                        <div class="field">
                            <label for="name" class="field-label">
                                Your Name <span class="field-required" aria-hidden="true">*</span>
                            </label>
                            <input id="name" type="text" v-model="form.name" required class="field-input"
                                :class="{ 'field-input--error': form.errors.name }" />
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>
                        <div class="field">
                            <label for="email" class="field-label">
                                Email Address <span class="field-required" aria-hidden="true">*</span>
                            </label>
                            <input id="email" type="email" v-model="form.email" required class="field-input"
                                :class="{ 'field-input--error': form.errors.email }" />
                            <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="field">
                        <label for="subject" class="field-label">
                            Subject <span class="field-optional">(optional)</span>
                        </label>
                        <input id="subject" type="text" v-model="form.subject" class="field-input"
                            :class="{ 'field-input--error': form.errors.subject }" />
                        <p v-if="form.errors.subject" class="field-error">{{ form.errors.subject }}</p>
                    </div>

                    <div class="field">
                        <label for="message" class="field-label">
                            Message <span class="field-required" aria-hidden="true">*</span>
                        </label>
                        <textarea id="message" v-model="form.message" rows="6" required
                            class="field-input field-textarea"
                            :class="{ 'field-input--error': form.errors.message }"></textarea>
                        <p v-if="form.errors.message" class="field-error">{{ form.errors.message }}</p>
                    </div>

                    <div class="cp-form-footer">
                        <button type="submit" :disabled="form.processing" class="btn-rose">
                            <svg v-if="!form.processing" width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 2 11 13" />
                                <path d="M22 2 15 22 11 13 2 9l20-7z" />
                            </svg>
                            <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="spin">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                            </svg>
                            {{ form.processing ? 'Sending...' : 'Send Message' }}
                        </button>
                    </div>

                </form>

                <!-- Contact info sidebar -->
                <aside class="cp-info">

                    <div class="cp-info-card">
                        <div class="info-block">
                            <div class="info-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-label">Email</p>
                                <a href="mailto:contact@chapterofyou.co.uk" class="info-value info-link">
                                    contact@chapterofyou.co.uk
                                </a>
                            </div>
                        </div>

                        <div class="info-divider"></div>

                        <div class="info-block">
                            <div class="info-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 6v6l4 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-label">Response Time</p>
                                <p class="info-value">Within 1–2 working days</p>
                            </div>
                        </div>

                        <div class="info-divider"></div>

                        <div class="info-block">
                            <div class="info-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                            </div>
                            <div>
                                <p class="info-label">Made with love</p>
                                <p class="info-value">Every message is read personally by me.</p>
                            </div>
                        </div>
                    </div>

                </aside>

            </div>
        </div>
    </main>

    <SuccessToast ref="successToastRef" />

    <Footer />
</template>

<style scoped>
.cp {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.cp-wrap {
    max-width: 960px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

/* ── Header ── */
.cp-header {
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5c9c7;
}

.cp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.4rem;
}

.cp-sub {
    font-size: 0.97rem;
    color: #6b4f4f;
    line-height: 1.6;
    max-width: 520px;
}

/* ── Layout grid ── */
.cp-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 760px) {
    .cp-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Form card ── */
.cp-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.75rem;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.cp-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.cp-card::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 10px;
    font-size: 0.9rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

/* ── Fields ── */
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 540px) {
    .field-row {
        grid-template-columns: 1fr;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #6b4f4f;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.field-required {
    color: #8c4a50;
}

.field-optional {
    font-weight: 400;
    text-transform: none;
    font-style: italic;
    color: #9a7070;
}

.field-input {
    padding: 0.7rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    resize: none;
    width: 100%;
}

.field-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.field-input--error {
    border-color: #c84040;
}

.field-textarea {
    resize: vertical;
    min-height: 140px;
}

.field-error {
    font-size: 0.8rem;
    color: #b54040;
}

.cp-form-footer {
    padding-top: 0.25rem;
    border-top: 1px solid #e5c9c7;
    margin-top: 0.25rem;
}

/* ── Info sidebar ── */
.cp-info {
    position: sticky;
    top: 88px;
}

.cp-info-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
}

.cp-info-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.cp-info-card::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 10px;
    font-size: 0.85rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.info-block {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
}

.info-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(140, 74, 80, 0.08);
    border: 1px solid #e5c9c7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8c4a50;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.info-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #8c4a50;
    margin-bottom: 0.2rem;
}

.info-value {
    font-size: 0.9rem;
    color: #2d1a1a;
    line-height: 1.45;
}

.info-link {
    text-decoration: none;
    color: #2d1a1a;
    transition: color 0.2s;
    word-break: break-all;
}

.info-link:hover {
    color: #8c4a50;
}

.info-divider {
    border-top: 1px solid #e5c9c7;
    margin: 1rem 0;
}

/* ── Button ── */
.btn-rose {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.7rem 1.6rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-rose:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Spinner animation */
.spin {
    animation: spin 0.9s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
