<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
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
import NavBar from '@/components/NavBar.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <NavBar />

    <Head title="Log in" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <AuthBase title="Log in to your account" description="Enter your email and password below to log in" class="!p-0">
        <main class="lp">
            <div class="lp-card">

                <!-- Header -->
                <div class="lp-header">
                    <h1 class="lp-title">Welcome back</h1>
                    <p class="lp-sub">Sign in to your Chapter of You account</p>
                </div>

                <!-- Status message -->
                <div v-if="status" class="lp-status">
                    {{ status }}
                </div>

                <Form v-bind="AuthenticatedSessionController.store.form()" :reset-on-success="['password']"
                    v-slot="{ errors, processing }" class="lp-form">
                    <!-- Email -->
                    <div class="field">
                        <Label for="email" class="field-label">Email Address</Label>
                        <Input id="email" type="email" name="email" required autofocus :tabindex="1"
                            autocomplete="email" placeholder="you@example.com" class="field-input" />
                        <InputError :message="errors.email" class="field-error" />
                    </div>

                    <!-- Password -->
                    <div class="field">
                        <div class="lp-pw-row">
                            <Label for="password" class="field-label">Password</Label>
                            <TextLink v-if="canResetPassword" :href="request()" :tabindex="5" class="lp-forgot">
                                Forgot password?
                            </TextLink>
                        </div>
                        <Input id="password" type="password" name="password" required :tabindex="2"
                            autocomplete="current-password" placeholder="••••••••" class="field-input" />
                        <InputError :message="errors.password" class="field-error" />
                    </div>

                    <!-- Remember me -->
                    <Label for="remember" class="lp-remember">
                        <Checkbox id="remember" name="remember" :tabindex="3"
                            class="border-copy data-[state=checked]:bg-[var(--primary)] data-[state=checked]:text-primary-content" />
                        <span>Remember me</span>
                    </Label>

                    <!-- Submit -->
                    <Button type="submit" :tabindex="4" :disabled="processing" class="btn-rose btn-rose--full"
                        data-test="login-button">
                        <LoaderCircle v-if="processing" class="lp-spinner" />
                        {{ processing ? 'Signing in...' : 'Sign in' }}
                    </Button>

                    <!-- Divider -->
                    <div class="lp-divider" aria-hidden="true">
                        <span></span>
                        <span class="lp-divider-text">or</span>
                        <span></span>
                    </div>

                    <!-- Google login -->
                    <a :href="route('socialite.redirect', 'google')" class="lp-google" data-test="google-login-button">
                        <svg class="lp-google-icon" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" width="20" height="20">
                            <defs>
                                <path id="A"
                                    d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z" />
                            </defs>
                            <clipPath id="B">
                                <use xlink:href="#A" />
                            </clipPath>
                            <g transform="matrix(.727273 0 0 .727273 -.954545 -1.45455)">
                                <path d="M0 37V11l17 13z" clip-path="url(#B)" fill="#fbbc05" />
                                <path d="M0 11l17 13 7-6.1L48 14V0H0z" clip-path="url(#B)" fill="#ea4335" />
                                <path d="M0 37l30-23 7.9 1L48 0v48H0z" clip-path="url(#B)" fill="#34a853" />
                                <path d="M48 48L17 24l-4-3 35-10z" clip-path="url(#B)" fill="#4285f4" />
                            </g>
                        </svg>
                        Continue with Google
                    </a>

                    <!-- Register link -->
                    <p class="lp-register">
                        Don't have an account?
                        <TextLink :href="register()" :tabindex="5" class="lp-register-link">
                            Create one
                        </TextLink>
                    </p>
                </Form>

            </div>
        </main>
    </AuthBase>
</template>

<style scoped>
/* ── Page ── */
.lp {
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
.lp-card {
    width: 100%;
    max-width: 420px;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: #fffafa;
    box-shadow: 0 4px 32px rgba(229, 201, 199, 0.45);
    overflow: hidden;
    position: relative;
}

/* Petal watermarks */
.lp-card::before {
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

.lp-card::after {
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
.lp-header {
    padding: 2rem 2rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #fdf4f3, #fff8f7);
    border-bottom: 1px solid #e5c9c7;
}

.lp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.9rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.25rem;
}

.lp-sub {
    font-size: 0.88rem;
    color: #6b4f4f;
}

/* ── Status ── */
.lp-status {
    margin: 1rem 2rem 0;
    padding: 0.65rem 1rem;
    border-radius: 10px;
    background: #f0faf0;
    border: 1px solid #a8d8b0;
    color: #2d7a3a;
    font-size: 0.88rem;
    text-align: center;
}

/* ── Form ── */
.lp-form {
    padding: 1.5rem 2rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
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

/* Override Input component styling */
:deep(.field-input),
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

:deep(input[type="email"]:focus),
:deep(input[type="password"]:focus) {
    border-color: #8c4a50 !important;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1) !important;
}

.field-error {
    font-size: 0.78rem;
    color: #b54040;
}

/* Password row */
.lp-pw-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.lp-forgot {
    font-size: 0.8rem !important;
    color: #8c4a50 !important;
    text-decoration: none !important;
    transition: color 0.2s !important;
}

.lp-forgot:hover {
    color: #6a3038 !important;
    text-decoration: underline !important;
}

/* Remember me */
.lp-remember {
    display: flex !important;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.88rem !important;
    color: #6b4f4f !important;
    cursor: pointer;
    font-weight: 400 !important;
}

/* ── Buttons ── */
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

.lp-spinner {
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

/* ── Divider ── */
.lp-divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.lp-divider span:not(.lp-divider-text) {
    flex: 1;
    height: 1px;
    background: #e5c9c7;
}

.lp-divider-text {
    font-size: 0.78rem;
    color: #9a7070;
    font-style: italic;
    flex: none;
}

/* ── Google button ── */
.lp-google {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    padding: 0.68rem 1.25rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: #fffafa;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.2s, border-color 0.2s, transform 0.2s;
    width: 100%;
}

.lp-google:hover {
    background: #f5e4e4;
    border-color: #c9a4a4;
    transform: translateY(-1px);
}

.lp-google-icon {
    flex-shrink: 0;
}

/* ── Register link ── */
.lp-register {
    text-align: center;
    font-size: 0.88rem;
    color: #6b4f4f;
    padding-top: 0.25rem;
    border-top: 1px solid #f0dcd8;
}

.lp-register-link {
    color: #8c4a50 !important;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.lp-register-link:hover {
    color: #6a3038 !important;
    text-decoration: underline;
}
</style>
