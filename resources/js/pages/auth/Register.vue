<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

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

    <Head title="Create Account" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="rp">
        <div class="rp-card">

            <!-- Header -->
            <div class="rp-header">
                <h1 class="rp-title">Create an account</h1>
                <p class="rp-sub">Join Chapter of You and start your self-care journey</p>
            </div>

            <form @submit.prevent="submit" class="rp-form">

                <!-- Name -->
                <div class="field">
                    <label for="name" class="field-label">Full Name</label>
                    <input id="name" v-model="form.name" type="text" required autofocus autocomplete="name"
                        placeholder="Your name" class="field-input"
                        :class="{ 'field-input--error': form.errors.name }" />
                    <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="field">
                    <label for="email" class="field-label">Email Address</label>
                    <input id="email" v-model="form.email" type="email" required autocomplete="email"
                        placeholder="you@example.com" class="field-input"
                        :class="{ 'field-input--error': form.errors.email }" />
                    <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                </div>

                <!-- Password -->
                <div class="field">
                    <label for="password" class="field-label">Password</label>
                    <input id="password" v-model="form.password" type="password" required autocomplete="new-password"
                        placeholder="••••••••" class="field-input"
                        :class="{ 'field-input--error': form.errors.password }" />
                    <p v-if="form.errors.password" class="field-error">{{ form.errors.password }}</p>
                </div>

                <!-- Confirm password -->
                <div class="field">
                    <label for="password_confirmation" class="field-label">Confirm Password</label>
                    <input id="password_confirmation" v-model="form.password_confirmation" type="password" required
                        autocomplete="new-password" placeholder="••••••••" class="field-input"
                        :class="{ 'field-input--error': form.errors.password_confirmation }" />
                    <p v-if="form.errors.password_confirmation" class="field-error">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <!-- Marketing opt-in -->
                <label class="rp-optin">
                    <input type="checkbox" v-model="form.marketing_opt_in" class="rp-optin-check" />
                    <span>
                        I'd like to receive updates, news and exclusive offers from Chapter of You.
                        <span class="rp-optin-note">(Optional, you can change this in your account at any time)</span>
                    </span>
                </label>

                <!-- Submit -->
                <button type="submit" :disabled="form.processing" class="btn-rose btn-rose--full">
                    <LoaderCircle v-if="form.processing" class="rp-spinner" />
                    {{ form.processing ? 'Creating account…' : 'Create account' }}
                </button>

                <!-- Login link -->
                <p class="rp-login">
                    Already have an account?
                    <Link :href="route('login')" class="rp-login-link">Sign in</Link>
                </p>

            </form>
        </div>
    </main>
</template>

<style scoped>
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

.rp-form {
    padding: 1.5rem 2rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    position: relative;
    z-index: 1;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b4f4f;
}

.field-input {
    padding: 0.65rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    width: 100%;
}

.field-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.field-input--error {
    border-color: #c84040;
}

.field-error {
    font-size: 0.78rem;
    color: #b54040;
}

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

.btn-rose {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.72rem 1.5rem;
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
    margin-top: 0.5rem;
}

.btn-rose:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
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

.rp-login {
    text-align: center;
    font-size: 0.88rem;
    color: #6b4f4f;
    padding-top: 0.75rem;
    border-top: 1px solid #f0dcd8;
}

.rp-login-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.rp-login-link:hover {
    color: #6a3038;
    text-decoration: underline;
}
</style>
