<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import CoyBreadcrumbs from '@/components/ui/coy/CoyBreadcrumbs.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);
const form = useForm({ name: '', email: '', subject: '', message: '' });

function submit() {
    form.post(route('contact.store'), {
        onSuccess: () => {
            successToastRef.value?.show('Message sent', 'check');
            form.reset();
        },
        preserveScroll: true,
    });
}

const seo = useSeoHead({
    title: 'Contact Me',
    description:
        "Get in touch with Chapter of You. I'd love to hear from you and will respond within 1 to 2 working days.",
    canonical: '/contact',
});
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />
    <main class="cp coy-storefront">
        <header class="coy-page-header">
            <div class="coy-container coy-page-header__inner">
                <div>
                    <CoyBreadcrumbs
                        :items="[
                            { label: 'Home', href: '/' },
                            { label: 'Contact' },
                        ]"
                    />
                    <h1 class="coy-page-header__title">Get in touch</h1>
                    <p class="coy-page-header__meta">
                        Questions about a fragrance or an order are always
                        welcome.
                    </p>
                </div>
            </div>
        </header>
        <div class="coy-container cp-content">
            <section class="cp-intro">
                <p class="coy-eyebrow">Send a message</p>
                <h2>How can I help?</h2>
                <p>
                    Fill in the form and I will get back to you as soon as I
                    can. For an existing order, include your order number in the
                    message.
                </p>
            </section>
            <div class="cp-grid">
                <form class="cp-form" @submit.prevent="submit">
                    <div class="field-row">
                        <div class="field">
                            <label for="name"
                                >Your name
                                <span aria-hidden="true">*</span></label
                            ><input
                                id="name"
                                v-model="form.name"
                                type="text"
                                autocomplete="name"
                                required
                                :aria-invalid="Boolean(form.errors.name)"
                                :aria-describedby="
                                    form.errors.name ? 'name-error' : undefined
                                "
                            />
                            <p
                                v-if="form.errors.name"
                                id="name-error"
                                class="field-error"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="field">
                            <label for="email"
                                >Email address
                                <span aria-hidden="true">*</span></label
                            ><input
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                :aria-invalid="Boolean(form.errors.email)"
                                :aria-describedby="
                                    form.errors.email
                                        ? 'email-error'
                                        : undefined
                                "
                            />
                            <p
                                v-if="form.errors.email"
                                id="email-error"
                                class="field-error"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>
                    <div class="field">
                        <label for="subject"
                            >Subject <small>Optional</small></label
                        ><input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            :aria-invalid="Boolean(form.errors.subject)"
                            :aria-describedby="
                                form.errors.subject
                                    ? 'subject-error'
                                    : undefined
                            "
                        />
                        <p
                            v-if="form.errors.subject"
                            id="subject-error"
                            class="field-error"
                        >
                            {{ form.errors.subject }}
                        </p>
                    </div>
                    <div class="field">
                        <label for="message"
                            >Message <span aria-hidden="true">*</span></label
                        ><textarea
                            id="message"
                            v-model="form.message"
                            rows="7"
                            required
                            :aria-invalid="Boolean(form.errors.message)"
                            :aria-describedby="
                                form.errors.message
                                    ? 'message-error'
                                    : undefined
                            "
                        />
                        <p
                            v-if="form.errors.message"
                            id="message-error"
                            class="field-error"
                        >
                            {{ form.errors.message }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        class="cp-submit"
                        :disabled="form.processing"
                    >
                        <svg
                            v-if="form.processing"
                            class="cp-spinner"
                            aria-hidden="true"
                            viewBox="0 0 24 24"
                        >
                            <path d="M21 12a9 9 0 1 1-6.2-8.6" /></svg
                        ><svg v-else aria-hidden="true" viewBox="0 0 24 24">
                            <path d="m22 2-7 20-4-9-9-4Z" />
                            <path d="M22 2 11 13" /></svg
                        >{{ form.processing ? 'Sending...' : 'Send message' }}
                    </button>
                </form>
                <aside class="cp-details" aria-label="Contact details">
                    <p class="coy-eyebrow">Contact details</p>
                    <h2>A personal reply</h2>
                    <p>
                        Every message comes directly to me. I usually reply
                        within 1 to 2 working days.
                    </p>
                    <dl>
                        <div>
                            <dt>Email</dt>
                            <dd>
                                <a href="mailto:contact@chapterofyou.co.uk"
                                    >contact@chapterofyou.co.uk</a
                                >
                            </dd>
                        </div>
                        <div>
                            <dt>Response time</dt>
                            <dd>Within 1 to 2 working days</dd>
                        </div>
                    </dl>
                    <div class="cp-note">
                        <svg aria-hidden="true" viewBox="0 0 24 24">
                            <path
                                d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1 7.8 7.7 7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8Z"
                            />
                        </svg>
                        <p>Every message is read personally.</p>
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
    min-height: 100vh;
    padding-top: var(--coy-nav-height);
    background: var(--coy-color-page);
}
.cp-content {
    padding-block: clamp(2.5rem, 5vw, 4rem) clamp(4rem, 7vw, 6rem);
}
.cp-intro {
    max-width: 43rem;
    margin-bottom: var(--coy-space-6);
}
.cp-intro h2,
.cp-details h2 {
    margin: var(--coy-space-1) 0 var(--coy-space-3);
    color: var(--coy-color-heading);
    font: 500 clamp(2rem, 4vw, 3rem) / 1.08 var(--coy-font-display);
}
.cp-intro > p:last-child,
.cp-details > p {
    margin: 0;
    line-height: 1.65;
}
.cp-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.55fr) minmax(18rem, 0.75fr);
    gap: clamp(2rem, 5vw, 4.5rem);
    align-items: start;
}
.cp-form {
    display: flex;
    flex-direction: column;
    gap: var(--coy-space-5);
    padding: clamp(1.5rem, 4vw, 2.5rem);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    box-shadow: var(--coy-shadow-sm);
}
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--coy-space-4);
}
.field {
    display: flex;
    flex-direction: column;
    gap: var(--coy-space-2);
}
.field label {
    color: var(--coy-color-heading);
    font-size: var(--coy-text-sm);
    font-weight: 600;
}
.field label span {
    color: var(--coy-color-accent);
}
.field label small {
    margin-left: var(--coy-space-1);
    color: var(--coy-color-text);
    font-size: 0.8rem;
    font-weight: 400;
}
.field input,
.field textarea {
    width: 100%;
    padding: 0.75rem 0.9rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    font: inherit;
    outline: none;
    transition:
        border-color var(--coy-duration-fast),
        box-shadow var(--coy-duration-fast);
}
.field input {
    min-height: var(--coy-control-height);
}
.field textarea {
    min-height: 10rem;
    resize: vertical;
}
.field input:focus,
.field textarea:focus {
    border-color: var(--coy-color-focus);
    box-shadow: var(--coy-shadow-focus);
}
.field input[aria-invalid='true'],
.field textarea[aria-invalid='true'] {
    border-color: var(--coy-color-error);
}
.field-error {
    margin: 0;
    color: var(--coy-color-error);
    font-size: 0.875rem;
}
.cp-submit {
    align-self: flex-start;
    min-height: var(--coy-control-height);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--coy-space-2);
    padding: 0.7rem 1.25rem;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border: 1px solid var(--coy-color-accent);
    border-radius: var(--coy-radius-pill);
    font: 600 var(--coy-text-sm) var(--coy-font-body);
    cursor: pointer;
}
.cp-submit:hover:not(:disabled) {
    background: var(--coy-color-accent-hover);
}
.cp-submit:disabled {
    opacity: 0.65;
    cursor: wait;
}
.cp-submit svg {
    width: 1rem;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 2;
}
.cp-details {
    position: sticky;
    top: calc(var(--coy-nav-height) + 1.5rem);
    padding-top: var(--coy-space-4);
    border-top: 2px solid var(--coy-color-heading);
}
.cp-details h2 {
    font-size: clamp(1.75rem, 3vw, 2.25rem);
}
.cp-details dl {
    margin: var(--coy-space-6) 0 0;
}
.cp-details dl > div {
    padding: var(--coy-space-4) 0;
    border-top: 1px solid var(--coy-color-border-soft);
}
.cp-details dt {
    color: var(--coy-color-accent);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: var(--coy-tracking-label);
    text-transform: uppercase;
}
.cp-details dd {
    margin: var(--coy-space-1) 0 0;
    color: var(--coy-color-heading);
    font-weight: 600;
    overflow-wrap: anywhere;
}
.cp-details a {
    color: inherit;
    text-underline-offset: 0.2rem;
}
.cp-note {
    display: flex;
    align-items: center;
    gap: var(--coy-space-3);
    margin-top: var(--coy-space-5);
    padding: var(--coy-space-4);
    background: var(--coy-color-surface-soft);
    border-radius: var(--coy-radius-md);
}
.cp-note svg {
    width: 1.25rem;
    flex: none;
    fill: none;
    stroke: var(--coy-color-accent);
    stroke-width: 1.8;
}
.cp-note p {
    margin: 0;
    font-size: 0.9rem;
}
.cp-spinner {
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
@media (max-width: 760px) {
    .cp-grid {
        grid-template-columns: 1fr;
    }
    .cp-details {
        position: static;
    }
    .field-row {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 520px) {
    .cp-submit {
        width: 100%;
    }
}
@media (prefers-reduced-motion: reduce) {
    .cp-spinner {
        animation: none;
    }
}
</style>
