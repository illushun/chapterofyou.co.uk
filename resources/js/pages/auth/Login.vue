<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import NavBar from '@/components/NavBar.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{ status?: string; canResetPassword: boolean }>();
</script>

<template>
    <NavBar />
    <Head title="Log in" />

    <AuthBase class="!p-0">
        <main class="lp coy-storefront">
            <div class="lp-shell coy-container">
                <section class="lp-welcome" aria-labelledby="login-welcome">
                    <p class="coy-eyebrow">Welcome back</p>
                    <h1 id="login-welcome">Your account, all in one place</h1>
                    <p>
                        Sign in to revisit saved fragrances, follow your orders
                        and keep checkout details ready for next time.
                    </p>
                    <ul>
                        <li>
                            <span aria-hidden="true">✓</span> View order history
                        </li>
                        <li>
                            <span aria-hidden="true">✓</span> Return to your
                            wishlist
                        </li>
                        <li>
                            <span aria-hidden="true">✓</span> Manage saved
                            addresses
                        </li>
                    </ul>
                </section>

                <section class="lp-card" aria-labelledby="login-title">
                    <div class="lp-header">
                        <p class="coy-eyebrow">Chapter of You</p>
                        <h2 id="login-title">Sign in</h2>
                        <p>Enter your account details below.</p>
                    </div>

                    <div v-if="status" class="lp-status" role="status">
                        {{ status }}
                    </div>

                    <Form
                        v-bind="AuthenticatedSessionController.store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="lp-form"
                    >
                        <div class="field">
                            <Label for="email" class="field-label"
                                >Email address</Label
                            >
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="field-input"
                            />
                            <InputError
                                :message="errors.email"
                                class="field-error"
                            />
                        </div>

                        <div class="field">
                            <div class="lp-password-row">
                                <Label for="password" class="field-label"
                                    >Password</Label
                                >
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    :tabindex="5"
                                    class="lp-forgot"
                                    >Forgot password?</TextLink
                                >
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="field-input"
                            />
                            <InputError
                                :message="errors.password"
                                class="field-error"
                            />
                        </div>

                        <Label for="remember" class="lp-remember">
                            <Checkbox
                                id="remember"
                                name="remember"
                                :tabindex="3"
                            />
                            <span>Remember me on this device</span>
                        </Label>

                        <Button
                            type="submit"
                            :tabindex="4"
                            :disabled="processing"
                            class="coy-button coy-button--primary lp-submit"
                            data-test="login-button"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="lp-spinner"
                            />
                            {{ processing ? 'Signing in...' : 'Sign in' }}
                        </Button>

                        <div class="lp-divider" aria-hidden="true">
                            <span></span><b>or</b><span></span>
                        </div>

                        <a
                            :href="route('socialite.redirect', 'google')"
                            class="coy-button coy-button--secondary lp-google"
                            data-test="google-login-button"
                        >
                            <svg aria-hidden="true" viewBox="0 0 32 32">
                                <path
                                    fill="#4285f4"
                                    d="M29.6 16.3c0-1-.1-2-.3-2.9H16v5.5h7.6a6.5 6.5 0 0 1-2.8 4.2v3.6h4.6c2.7-2.5 4.2-6.1 4.2-10.4Z"
                                />
                                <path
                                    fill="#34a853"
                                    d="M16 30c3.8 0 7.1-1.3 9.4-3.4L20.8 23c-1.3.9-2.9 1.4-4.8 1.4-3.7 0-6.8-2.5-7.9-5.8H3.4v3.7A14.2 14.2 0 0 0 16 30Z"
                                />
                                <path
                                    fill="#fbbc05"
                                    d="M8.1 18.6a8.5 8.5 0 0 1 0-5.2V9.7H3.4a14.2 14.2 0 0 0 0 12.6l4.7-3.7Z"
                                />
                                <path
                                    fill="#ea4335"
                                    d="M16 7.6c2.1 0 4 .7 5.5 2.1l4.1-4.1A13.8 13.8 0 0 0 16 2 14.2 14.2 0 0 0 3.4 9.7l4.7 3.7c1.1-3.3 4.2-5.8 7.9-5.8Z"
                                />
                            </svg>
                            Continue with Google
                        </a>

                        <p class="lp-register">
                            New to Chapter of You?
                            <TextLink :href="register()" :tabindex="5"
                                >Create an account</TextLink
                            >
                        </p>
                    </Form>
                </section>
            </div>
        </main>
    </AuthBase>
</template>

<style scoped>
.lp {
    min-height: 100svh;
    padding: calc(var(--coy-nav-height) + clamp(2rem, 6vw, 5rem)) 0
        clamp(3rem, 7vw, 6rem);
    display: grid;
    align-items: center;
    background: var(--coy-color-page);
}
.lp-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(22rem, 28rem);
    gap: clamp(3rem, 8vw, 8rem);
    align-items: center;
}
.lp-welcome {
    max-width: 40rem;
}
.lp-welcome h1 {
    max-width: 12ch;
    margin: var(--coy-space-2) 0 var(--coy-space-5);
    color: var(--coy-color-heading);
    font: 500 clamp(3rem, 6vw, 5rem) / 0.98 var(--coy-font-display);
    letter-spacing: -0.035em;
}
.lp-welcome > p:last-of-type {
    max-width: 36rem;
    margin: 0;
    font-size: var(--coy-text-lead);
    line-height: 1.6;
}
.lp-welcome ul {
    display: grid;
    gap: var(--coy-space-3);
    margin: var(--coy-space-6) 0 0;
    padding: 0;
    list-style: none;
}
.lp-welcome li {
    display: flex;
    align-items: center;
    gap: var(--coy-space-3);
    color: var(--coy-color-heading);
    font-weight: 600;
}
.lp-welcome li span {
    width: 1.5rem;
    height: 1.5rem;
    display: grid;
    place-items: center;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: 50%;
    font-size: 0.75rem;
}
.lp-card {
    overflow: hidden;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-xl);
    box-shadow: var(--coy-shadow-md);
}
.lp-header {
    padding: clamp(1.5rem, 4vw, 2.25rem) clamp(1.5rem, 4vw, 2.25rem)
        var(--coy-space-5);
    background: var(--coy-color-surface-soft);
    border-bottom: 1px solid var(--coy-color-border-soft);
}
.lp-header h2 {
    margin: var(--coy-space-1) 0 var(--coy-space-2);
    color: var(--coy-color-heading);
    font: 500 2.25rem/1.05 var(--coy-font-display);
}
.lp-header > p:last-child {
    margin: 0;
}
.lp-status {
    margin: var(--coy-space-5) clamp(1.5rem, 4vw, 2.25rem) 0;
    padding: var(--coy-space-3) var(--coy-space-4);
    color: var(--coy-color-success);
    background: var(--coy-color-success-soft);
    border: 1px solid rgb(39 100 55/25%);
    border-radius: var(--coy-radius-md);
    font-size: 0.9rem;
}
.lp-form {
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
.field-error {
    font-size: 0.875rem;
}
.lp-password-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--coy-space-3);
}
.lp-forgot,
.lp-register a {
    color: var(--coy-color-accent);
    font-size: 0.9rem;
    font-weight: 600;
    text-underline-offset: 0.2rem;
}
.lp-remember {
    display: flex;
    align-items: center;
    gap: var(--coy-space-2);
    color: var(--coy-color-text);
    font: 400 0.95rem var(--coy-font-body);
    cursor: pointer;
}
.lp-submit,
.lp-google {
    width: 100%;
}
.lp-spinner {
    width: 1rem;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.lp-divider {
    display: flex;
    align-items: center;
    gap: var(--coy-space-3);
    color: var(--coy-color-text);
    font-size: 0.8rem;
}
.lp-divider span {
    height: 1px;
    flex: 1;
    background: var(--coy-color-border-soft);
}
.lp-divider b {
    font-weight: 400;
}
.lp-google svg {
    width: 1.25rem;
    flex: none;
}
.lp-register {
    margin: var(--coy-space-1) 0 0;
    padding-top: var(--coy-space-4);
    border-top: 1px solid var(--coy-color-border-soft);
    font-size: 0.9rem;
    text-align: center;
}
@media (max-width: 820px) {
    .lp-shell {
        grid-template-columns: 1fr;
    }
    .lp-welcome {
        display: none;
    }
    .lp-card {
        width: min(100%, 28rem);
        margin-inline: auto;
    }
}
@media (max-width: 520px) {
    .lp {
        align-items: start;
        padding-top: calc(var(--coy-nav-height) + var(--coy-space-5));
    }
    .lp-card {
        border-radius: var(--coy-radius-lg);
    }
    .lp-header,
    .lp-form {
        padding-right: var(--coy-space-5);
        padding-left: var(--coy-space-5);
    }
}
@media (prefers-reduced-motion: reduce) {
    .lp-spinner {
        animation: none;
    }
}
</style>
