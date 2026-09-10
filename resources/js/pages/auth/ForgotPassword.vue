<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Check, LoaderCircle, Mail } from 'lucide-vue-next';

defineProps<{ status?: string }>();

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <NavBar />
    <Head title="Forgot password" />

    <AuthBase class="!p-0">
        <main class="fp coy-storefront">
            <div class="fp-shell coy-container">
                <section class="fp-intro" aria-labelledby="recovery-intro">
                    <p class="coy-eyebrow">Account recovery</p>
                    <h1 id="recovery-intro">Let’s get you back in</h1>
                    <p>
                        Enter the email address linked to your account and we’ll
                        send you a secure password reset link.
                    </p>
                    <div class="fp-note">
                        <span aria-hidden="true"><Check /></span>
                        <p>
                            The link will arrive by email. If it is not in your
                            inbox, check your spam or junk folder.
                        </p>
                    </div>
                </section>

                <section class="fp-card" aria-labelledby="recovery-title">
                    <header class="fp-header">
                        <span class="fp-icon" aria-hidden="true"><Mail /></span>
                        <div>
                            <p class="coy-eyebrow">Reset password</p>
                            <h2 id="recovery-title">Forgot your password?</h2>
                            <p>We’ll email you a link to choose a new one.</p>
                        </div>
                    </header>

                    <div v-if="status" class="fp-status" role="status">
                        <Check aria-hidden="true" />
                        <span>{{ status }}</span>
                    </div>

                    <form class="fp-form" @submit.prevent="submit">
                        <div class="field">
                            <Label for="email" class="field-label"
                                >Email address</Label
                            >
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="field-input"
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

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="coy-button coy-button--primary fp-submit"
                            data-test="email-password-reset-link-button"
                        >
                            <LoaderCircle
                                v-if="form.processing"
                                class="fp-spinner"
                            />
                            {{
                                form.processing
                                    ? 'Sending link...'
                                    : 'Send reset link'
                            }}
                        </Button>

                        <Link :href="route('login')" class="fp-back">
                            <ArrowLeft aria-hidden="true" />
                            Back to sign in
                        </Link>
                    </form>
                </section>
            </div>
        </main>
    </AuthBase>
</template>

<style scoped>
.fp {
    min-height: 100svh;
    display: grid;
    align-items: center;
    padding: calc(var(--coy-nav-height) + clamp(2rem, 6vw, 5rem)) 0
        clamp(3rem, 7vw, 6rem);
    background: var(--coy-color-page);
}
.fp-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(22rem, 28rem);
    gap: clamp(3rem, 8vw, 8rem);
    align-items: center;
}
.fp-intro {
    max-width: 40rem;
}
.fp-intro h1 {
    max-width: 10ch;
    margin: var(--coy-space-2) 0 var(--coy-space-5);
    color: var(--coy-color-heading);
    font: 500 clamp(3rem, 6vw, 5rem) / 0.98 var(--coy-font-display);
    letter-spacing: -0.035em;
}
.fp-intro > p:last-of-type {
    max-width: 35rem;
    margin: 0;
    font-size: var(--coy-text-lead);
    line-height: 1.6;
}
.fp-note {
    display: flex;
    align-items: flex-start;
    gap: var(--coy-space-3);
    max-width: 34rem;
    margin-top: var(--coy-space-6);
    padding-top: var(--coy-space-5);
    border-top: 1px solid var(--coy-color-border);
}
.fp-note > span {
    width: 1.5rem;
    height: 1.5rem;
    display: grid;
    flex: none;
    place-items: center;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: 50%;
}
.fp-note svg {
    width: 0.85rem;
}
.fp-note p {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.55;
}
.fp-card {
    overflow: hidden;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-xl);
    box-shadow: var(--coy-shadow-md);
}
.fp-header {
    display: flex;
    align-items: flex-start;
    gap: var(--coy-space-4);
    padding: clamp(1.5rem, 4vw, 2.25rem);
    background: var(--coy-color-surface-soft);
    border-bottom: 1px solid var(--coy-color-border-soft);
}
.fp-icon {
    width: 2.75rem;
    height: 2.75rem;
    display: grid;
    flex: none;
    place-items: center;
    color: var(--coy-color-accent);
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: 50%;
}
.fp-icon svg {
    width: 1.15rem;
}
.fp-header h2 {
    margin: var(--coy-space-1) 0 var(--coy-space-2);
    color: var(--coy-color-heading);
    font: 500 2rem/1.05 var(--coy-font-display);
}
.fp-header div > p:last-child {
    margin: 0;
    font-size: 0.9rem;
}
.fp-status {
    display: flex;
    align-items: flex-start;
    gap: var(--coy-space-2);
    margin: var(--coy-space-5) clamp(1.5rem, 4vw, 2.25rem) 0;
    padding: var(--coy-space-3) var(--coy-space-4);
    color: var(--coy-color-success);
    background: var(--coy-color-success-soft);
    border: 1px solid rgb(39 100 55 / 25%);
    border-radius: var(--coy-radius-md);
    font-size: 0.875rem;
    line-height: 1.5;
}
.fp-status svg {
    width: 1rem;
    flex: none;
    margin-top: 0.15rem;
}
.fp-form {
    display: flex;
    flex-direction: column;
    gap: var(--coy-space-4);
    padding: clamp(1.5rem, 4vw, 2.25rem);
}
.field {
    display: flex;
    flex-direction: column;
    gap: var(--coy-space-2);
}
.field-label {
    color: var(--coy-color-heading);
    font: 600 var(--coy-text-sm) var(--coy-font-body);
}
:deep(.field-input) {
    width: 100%;
    min-height: var(--coy-control-height);
    padding: 0.7rem 1rem;
    color: var(--coy-color-heading);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-md);
    font: inherit;
    box-shadow: none;
}
:deep(.field-input:focus) {
    border-color: var(--coy-color-focus);
    box-shadow: var(--coy-shadow-focus);
}
:deep(.field-input[aria-invalid='true']) {
    border-color: var(--coy-color-error);
}
.field-error {
    margin: 0;
    color: var(--coy-color-error);
    font-size: 0.8rem;
    line-height: 1.4;
}
.fp-submit {
    width: 100%;
}
.fp-spinner {
    width: 1rem;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.fp-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--coy-space-2);
    padding-top: var(--coy-space-4);
    color: var(--coy-color-accent);
    border-top: 1px solid var(--coy-color-border-soft);
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
}
.fp-back:hover {
    text-decoration: underline;
    text-underline-offset: 0.2rem;
}
.fp-back svg {
    width: 1rem;
}
@media (max-width: 820px) {
    .fp-shell {
        grid-template-columns: 1fr;
    }
    .fp-intro {
        display: none;
    }
    .fp-card {
        width: min(100%, 28rem);
        margin-inline: auto;
    }
}
@media (max-width: 520px) {
    .fp {
        align-items: start;
        padding-top: calc(var(--coy-nav-height) + var(--coy-space-5));
    }
    .fp-shell {
        width: calc(100% - 1rem);
    }
    .fp-card {
        border-radius: var(--coy-radius-lg);
    }
    .fp-header,
    .fp-form {
        padding-right: var(--coy-space-5);
        padding-left: var(--coy-space-5);
    }
    .fp-status {
        margin-right: var(--coy-space-5);
        margin-left: var(--coy-space-5);
    }
}
@media (prefers-reduced-motion: reduce) {
    .fp-spinner {
        animation: none;
    }
}
</style>
