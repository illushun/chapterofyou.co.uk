    <script setup lang="ts">
    import NavBar from '@/components/NavBar.vue';
    import { Head, Link, useForm } from '@inertiajs/vue3';
    import { LoaderCircle } from 'lucide-vue-next';

    defineProps<{ status?: string }>();

    const form = useForm({ email: '' });

    const submit = () => {
        form.post(route('password.email'));
    };
</script>

<template>
    <NavBar />

    <Head title="Forgot Password" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="fp">
        <div class="fp-card">

            <!-- Header -->
            <div class="fp-header">
                <div class="fp-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" />
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                    </svg>
                </div>
                <h1 class="fp-title">Forgot your password?</h1>
                <p class="fp-sub">
                    No problem. Enter your email address and I'll send you a
                    link to reset it.
                </p>
            </div>

            <!-- Success status -->
            <div v-if="status" class="fp-status">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                </svg>
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="fp-form">

                <div class="field">
                    <label for="email" class="field-label">Email Address</label>
                    <input id="email" v-model="form.email" type="email" required autofocus autocomplete="email"
                        placeholder="you@example.com" class="field-input"
                        :class="{ 'field-input--error': form.errors.email }" />
                    <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="btn-rose btn-rose--full">
                    <LoaderCircle v-if="form.processing" class="fp-spinner" />
                    {{ form.processing ? 'Sending…' : 'Send Reset Link' }}
                </button>

            </form>

            <div class="fp-footer">
                <span>Remember your password?</span>
                <Link :href="route('login')" class="fp-link">Sign in</Link>
            </div>

        </div>
    </main>
</template>

<style scoped>
.fp {
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

.fp-card {
    width: 100%;
    max-width: 400px;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: #fffafa;
    box-shadow: 0 4px 32px rgba(229, 201, 199, 0.45);
    overflow: hidden;
    position: relative;
}

.fp-card::before {
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

.fp-card::after {
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

/* Header */
.fp-header {
    padding: 2rem 2rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, #fdf4f3, #fff8f7);
    border-bottom: 1px solid #e5c9c7;
}

.fp-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 14px rgba(168, 80, 88, 0.25);
}

.fp-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.75rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.4rem;
}

.fp-sub {
    font-size: 0.88rem;
    color: #6b4f4f;
    line-height: 1.6;
    max-width: 280px;
    margin: 0 auto;
}

/* Status */
.fp-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 1rem 1.5rem 0;
    padding: 0.75rem 1rem;
    background: #f0faf0;
    border: 1px solid #a8d8b0;
    border-radius: 10px;
    font-size: 0.88rem;
    color: #2d7a3a;
    font-weight: 500;
}

/* Form */
.fp-form {
    padding: 1.5rem 2rem 1.25rem;
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
    margin-top: 0.25rem;
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

.fp-spinner {
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

/* Footer */
.fp-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0 2rem 1.5rem;
    font-size: 0.88rem;
    color: #6b4f4f;
    border-top: 1px solid #f0dcd8;
    padding-top: 1rem;
    position: relative;
    z-index: 1;
}

.fp-link {
    color: #8c4a50;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.fp-link:hover {
    color: #6a3038;
    text-decoration: underline;
}
</style>
