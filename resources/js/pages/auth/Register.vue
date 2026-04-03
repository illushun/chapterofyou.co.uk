<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import NavBar from '@/components/NavBar.vue';
</script>

<template>
    <NavBar />

    <Head title="Create Account" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <AuthBase title="Create an account" description="Enter your details below to create your account" class="!p-0">
        <main class="rp">
            <div class="rp-card">

                <!-- Header -->
                <div class="rp-header">
                    <h1 class="rp-title">Create an account</h1>
                    <p class="rp-sub">Join Chapter of You and start your self-care journey</p>
                </div>

                <Form v-bind="RegisteredUserController.store.form()"
                    :reset-on-success="['password', 'password_confirmation']" v-slot="{ errors, processing }"
                    class="rp-form">
                    <!-- Name -->
                    <div class="field">
                        <Label for="name" class="field-label">Full Name</Label>
                        <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name"
                            placeholder="Your name" class="field-input" />
                        <InputError :message="errors.name" class="field-error" />
                    </div>

                    <!-- Email -->
                    <div class="field">
                        <Label for="email" class="field-label">Email Address</Label>
                        <Input id="email" type="email" required :tabindex="2" autocomplete="email" name="email"
                            placeholder="you@example.com" class="field-input" />
                        <InputError :message="errors.email" class="field-error" />
                    </div>

                    <!-- Password -->
                    <div class="field">
                        <Label for="password" class="field-label">Password</Label>
                        <Input id="password" type="password" required :tabindex="3" autocomplete="new-password"
                            name="password" placeholder="••••••••" class="field-input" />
                        <InputError :message="errors.password" class="field-error" />
                    </div>

                    <!-- Confirm password -->
                    <div class="field">
                        <Label for="password_confirmation" class="field-label">Confirm Password</Label>
                        <Input id="password_confirmation" type="password" required :tabindex="4"
                            autocomplete="new-password" name="password_confirmation" placeholder="••••••••"
                            class="field-input" />
                        <InputError :message="errors.password_confirmation" class="field-error" />
                    </div>

                    <!-- Marketing opt-in -->
                    <label class="rp-optin">
                        <input type="checkbox" name="marketing_opt_in" v-model="form.marketing_opt_in"
                            class="rp-optin-check" />
                        <span>
                            I'd like to receive updates, news and exclusive offers from Chapter of You.
                            <span class="rp-optin-note">(Optional — you can change this in your account at any
                                time)</span>
                        </span>
                    </label>

                    <!-- Submit -->
                    <Button type="submit" tabindex="5" :disabled="processing" class="btn-rose btn-rose--full"
                        data-test="register-user-button">
                        <LoaderCircle v-if="processing" class="rp-spinner" />
                        {{ processing ? 'Creating account...' : 'Create account' }}
                    </Button>

                    <!-- Login link -->
                    <p class="rp-login">
                        Already have an account?
                        <TextLink :href="login()" :tabindex="6" class="rp-login-link">
                            Sign in
                        </TextLink>
                    </p>
                </Form>

            </div>
        </main>
    </AuthBase>
</template>

<style scoped>
/* ── Page ── */
.rp {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    display: flex;
    align-items: center;
    justify-content: center;
    padding-left: 1.25rem;
    padding-right: 1.25rem;
    padding-bottom: 3rem;
}

/* ── Card ── */
.rp-card {
    width: 100%;
    max-width: 420px;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: #fffafa;
    box-shadow: 0 4px 32px rgba(229, 201, 199, 0.45);
    overflow: hidden;
    position: relative;
}

.rp-card::before {
    content: '✿';
    position: absolute;
    bottom: -8px;
    right: 10px;
    font-size: 4rem;
    color: #c9a4a4;
    opacity: 0.1;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

.rp-card::after {
    content: '✿';
    position: absolute;
    top: 8px;
    left: 12px;
    font-size: 0.9rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
    z-index: 0;
}

/* ── Header band ── */
.rp-header {
    padding: 2rem 2rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #fdf4f3, #fff8f7);
    border-bottom: 1px solid #e5c9c7;
}

.rp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.9rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.25rem;
}

.rp-sub {
    font-size: 0.88rem;
    color: #6b4f4f;
}

/* ── Form ── */
.rp-form {
    padding: 1.5rem 2rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    position: relative;
    z-index: 1;
}

/* ── Fields ── */
.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b4f4f !important;
}

:deep(input[type="text"]),
:deep(input[type="email"]),
:deep(input[type="password"]) {
    padding: 0.65rem 0.9rem !important;
    border: 1px solid #e5c9c7 !important;
    border-radius: 10px !important;
    background: #fdf4f3 !important;
    color: #2d1a1a !important;
    font-family: 'Nunito', sans-serif !important;
    font-size: 0.92rem !important;
    outline: none !important;
    transition: border-color 0.2s, box-shadow 0.2s !important;
    box-shadow: none !important;
}

:deep(input[type="text"]:focus),
:deep(input[type="email"]:focus),
:deep(input[type="password"]:focus) {
    border-color: #8c4a50 !important;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1) !important;
}

.field-error {
    font-size: 0.78rem;
    color: #b54040;
}

/* ── Opt-in ── */
.rp-optin {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    font-size: 0.85rem;
    color: #6b4f4f;
    cursor: pointer;
    line-height: 1.5;
    padding: 0.75rem 0.85rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    transition: background 0.15s;
}

.rp-optin:hover {
    background: #faeaea;
}

.rp-optin-check {
    width: 15px;
    height: 15px;
    accent-color: #8c4a50;
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 2px;
}

.rp-optin-note {
    display: block;
    font-size: 0.75rem;
    color: #9a7070;
    font-style: italic;
    margin-top: 0.15rem;
}

/* ── Button ── */
.btn-rose {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.72rem 1.5rem;
    border-radius: 999px;
    border: 1px solid #a85058 !important;
    background: linear-gradient(135deg, #c47078, #a85058) !important;
    color: #fff !important;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s !important;
    height: auto !important;
    margin-top: 0.5rem;
}

.btn-rose:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28) !important;
}

.btn-rose:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.btn-rose--full {
    width: 100%;
}

.rp-spinner {
    width: 15px;
    height: 15px;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* ── Login link ── */
.rp-login {
    text-align: center;
    font-size: 0.88rem;
    color: #6b4f4f;
    padding-top: 0.75rem;
    border-top: 1px solid #f0dcd8;
}

.rp-login-link {
    color: #8c4a50 !important;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.rp-login-link:hover {
    color: #6a3038 !important;
    text-decoration: underline;
}
</style>
