<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Check, LoaderCircle } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    marketing_opt_in: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <NavBar />
    <Head title="Create account" />

    <AuthBase class="!p-0">
        <main class="rp coy-storefront">
            <div class="rp-shell coy-container">
                <section class="rp-welcome" aria-labelledby="register-welcome">
                    <p class="coy-eyebrow">Your next chapter</p>
                    <h1 id="register-welcome">A more personal way to shop</h1>
                    <p>
                        Create an account to keep the fragrances you love close
                        and make every return visit a little easier.
                    </p>
                    <ul>
                        <li>
                            <span aria-hidden="true"><Check /></span> Save
                            favourites to your wishlist
                        </li>
                        <li>
                            <span aria-hidden="true"><Check /></span> Keep track
                            of every order
                        </li>
                        <li>
                            <span aria-hidden="true"><Check /></span> Checkout
                            more quickly next time
                        </li>
                    </ul>
                </section>

                <section class="rp-card" aria-labelledby="register-title">
                    <header class="rp-header">
                        <p class="coy-eyebrow">Chapter of You</p>
                        <h2 id="register-title">Create your account</h2>
                        <p>Just a few details to get started.</p>
                    </header>

                    <form class="rp-form" @submit.prevent="submit">
                        <div class="rp-fields">
                            <div class="field">
                                <Label for="name" class="field-label"
                                    >Full name</Label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="name"
                                    placeholder="Your name"
                                    class="field-input"
                                    :aria-invalid="Boolean(form.errors.name)"
                                    :aria-describedby="
                                        form.errors.name
                                            ? 'name-error'
                                            : undefined
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
                                <Label for="email" class="field-label"
                                    >Email address</Label
                                >
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
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

                            <div class="field">
                                <Label for="password" class="field-label"
                                    >Password</Label
                                >
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Create a password"
                                    class="field-input"
                                    :aria-invalid="
                                        Boolean(form.errors.password)
                                    "
                                    :aria-describedby="
                                        form.errors.password
                                            ? 'password-error'
                                            : 'password-hint'
                                    "
                                />
                                <p
                                    v-if="form.errors.password"
                                    id="password-error"
                                    class="field-error"
                                >
                                    {{ form.errors.password }}
                                </p>
                                <p v-else id="password-hint" class="field-hint">
                                    Use at least 8 characters.
                                </p>
                            </div>

                            <div class="field">
                                <Label
                                    for="password_confirmation"
                                    class="field-label"
                                    >Confirm password</Label
                                >
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Repeat your password"
                                    class="field-input"
                                    :aria-invalid="
                                        Boolean(
                                            form.errors.password_confirmation,
                                        )
                                    "
                                    :aria-describedby="
                                        form.errors.password_confirmation
                                            ? 'password-confirmation-error'
                                            : undefined
                                    "
                                />
                                <p
                                    v-if="form.errors.password_confirmation"
                                    id="password-confirmation-error"
                                    class="field-error"
                                >
                                    {{ form.errors.password_confirmation }}
                                </p>
                            </div>
                        </div>

                        <label class="rp-optin">
                            <input
                                v-model="form.marketing_opt_in"
                                type="checkbox"
                            />
                            <span
                                ><strong>Keep me in the know</strong>Receive
                                occasional news, inspiration and exclusive
                                offers. You can change this at any time.</span
                            >
                        </label>

                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="coy-button coy-button--primary rp-submit"
                            data-test="register-button"
                        >
                            <LoaderCircle
                                v-if="form.processing"
                                class="rp-spinner"
                            />
                            {{
                                form.processing
                                    ? 'Creating account...'
                                    : 'Create account'
                            }}
                        </Button>

                        <p class="rp-login">
                            Already have an account?
                            <Link :href="route('login')">Sign in</Link>
                        </p>
                    </form>
                </section>
            </div>
        </main>
    </AuthBase>
</template>

<style scoped>
.rp {
    min-height: 100svh;
    display: grid;
    align-items: center;
    padding: calc(var(--coy-nav-height) + clamp(2rem, 5vw, 4rem)) 0
        clamp(3rem, 7vw, 6rem);
    background: var(--coy-color-page);
}
.rp-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(28rem, 34rem);
    gap: clamp(3rem, 8vw, 8rem);
    align-items: center;
}
.rp-welcome {
    max-width: 40rem;
}
.rp-welcome h1 {
    max-width: 11ch;
    margin: var(--coy-space-2) 0 var(--coy-space-5);
    color: var(--coy-color-heading);
    font: 500 clamp(3rem, 6vw, 5rem) / 0.98 var(--coy-font-display);
    letter-spacing: -0.035em;
}
.rp-welcome > p:last-of-type {
    max-width: 35rem;
    margin: 0;
    font-size: var(--coy-text-lead);
    line-height: 1.6;
}
.rp-welcome ul {
    display: grid;
    gap: var(--coy-space-3);
    margin: var(--coy-space-6) 0 0;
    padding: 0;
    list-style: none;
}
.rp-welcome li {
    display: flex;
    align-items: center;
    gap: var(--coy-space-3);
    color: var(--coy-color-heading);
    font-weight: 600;
}
.rp-welcome li span {
    width: 1.5rem;
    height: 1.5rem;
    display: grid;
    flex: none;
    place-items: center;
    color: var(--coy-color-on-accent);
    background: var(--coy-color-accent);
    border-radius: 50%;
}
.rp-welcome li svg {
    width: 0.85rem;
}
.rp-card {
    overflow: hidden;
    background: var(--coy-color-surface);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-xl);
    box-shadow: var(--coy-shadow-md);
}
.rp-header {
    padding: clamp(1.5rem, 4vw, 2.25rem) clamp(1.5rem, 4vw, 2.25rem)
        var(--coy-space-5);
    background: var(--coy-color-surface-soft);
    border-bottom: 1px solid var(--coy-color-border-soft);
}
.rp-header h2 {
    margin: var(--coy-space-1) 0 var(--coy-space-2);
    color: var(--coy-color-heading);
    font: 500 2.25rem/1.05 var(--coy-font-display);
}
.rp-header > p:last-child {
    margin: 0;
}
.rp-form {
    display: flex;
    flex-direction: column;
    gap: var(--coy-space-5);
    padding: clamp(1.5rem, 4vw, 2.25rem);
}
.rp-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--coy-space-4);
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
.field-error,
.field-hint {
    margin: 0;
    font-size: 0.8rem;
    line-height: 1.4;
}
.field-error {
    color: var(--coy-color-error);
}
.field-hint {
    color: var(--coy-color-text);
}
.rp-optin {
    display: flex;
    align-items: flex-start;
    gap: var(--coy-space-3);
    padding: var(--coy-space-4);
    color: var(--coy-color-text);
    background: var(--coy-color-page);
    border: 1px solid var(--coy-color-border-soft);
    border-radius: var(--coy-radius-md);
    font-size: 0.85rem;
    line-height: 1.5;
    cursor: pointer;
    transition: border-color var(--coy-duration-fast) var(--coy-ease);
}
.rp-optin:hover {
    border-color: var(--coy-color-border);
}
.rp-optin input {
    width: 1rem;
    height: 1rem;
    flex: none;
    margin-top: 0.2rem;
    accent-color: var(--coy-color-accent);
}
.rp-optin strong {
    display: block;
    margin-bottom: var(--coy-space-1);
    color: var(--coy-color-heading);
    font-size: 0.9rem;
}
.rp-submit {
    width: 100%;
}
.rp-spinner {
    width: 1rem;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.rp-login {
    margin: 0;
    padding-top: var(--coy-space-4);
    border-top: 1px solid var(--coy-color-border-soft);
    font-size: 0.9rem;
    text-align: center;
}
.rp-login a {
    color: var(--coy-color-accent);
    font-weight: 600;
    text-underline-offset: 0.2rem;
}
@media (max-width: 960px) {
    .rp-shell {
        grid-template-columns: 1fr;
    }
    .rp-welcome {
        display: none;
    }
    .rp-card {
        width: min(100%, 34rem);
        margin-inline: auto;
    }
}
@media (max-width: 620px) {
    .rp {
        align-items: start;
        padding-top: calc(var(--coy-nav-height) + var(--coy-space-5));
    }
    .rp-shell {
        width: calc(100% - 1rem);
    }
    .rp-card {
        border-radius: var(--coy-radius-lg);
    }
    .rp-header,
    .rp-form {
        padding-right: var(--coy-space-5);
        padding-left: var(--coy-space-5);
    }
    .rp-fields {
        grid-template-columns: 1fr;
    }
}
@media (prefers-reduced-motion: reduce) {
    .rp-spinner {
        animation: none;
    }
}
</style>
