<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import NavBar from '@/components/NavBar.vue';

const props = defineProps<{
    user: { name: string; email: string };
    alreadyOut?: boolean;
    confirmed?: boolean;
}>();

// Hit the confirm route (POST to signed URL preserved in the current URL)
const confirm = () => {
    router.post(window.location.href.replace('/unsubscribe/', '/unsubscribe/confirm/'), {}, {
        preserveUrl: true,
    });
};
</script>

<template>
    <NavBar />

    <Head title="Unsubscribe" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;1,400&family=Nunito:wght@400;500;600&display=swap"
        rel="stylesheet" />

    <main class="us">
        <div class="us-card">

            <!-- Already confirmed -->
            <template v-if="confirmed">
                <div class="us-icon us-icon--done">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                </div>
                <h1 class="us-title">You're unsubscribed</h1>
                <p class="us-body">
                    <strong>{{ user.name }}</strong>, you've been removed from our marketing list. You won't receive any
                    further broadcast emails.
                </p>
                <p class="us-note">
                    You can re-enable marketing emails at any time from your account settings.
                </p>
                <a href="/account" class="btn-rose">Go to Account Settings</a>
            </template>

            <!-- Already opted out -->
            <template v-else-if="alreadyOut">
                <div class="us-icon us-icon--info">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 8v4M12 16h.01" />
                    </svg>
                </div>
                <h1 class="us-title">Already unsubscribed</h1>
                <p class="us-body">
                    <strong>{{ user.email }}</strong> is already opted out of marketing emails from Chapter of You.
                </p>
                <a href="/" class="us-ghost-btn">Return to homepage</a>
            </template>

            <!-- Confirm prompt -->
            <template v-else>
                <div class="us-icon us-icon--warn">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                        <polyline points="22,6 12,13 2,6" />
                    </svg>
                </div>
                <h1 class="us-title">Unsubscribe from marketing emails?</h1>
                <p class="us-body">
                    You're about to remove <strong>{{ user.email }}</strong> from the Chapter of You marketing list.
                    You'll no longer receive newsletters, promotions, or updates.
                </p>
                <p class="us-note">
                    You'll still receive order confirmation and dispatch emails — this only affects marketing.
                </p>
                <div class="us-actions">
                    <button @click="confirm" class="btn-rose">
                        Yes, unsubscribe me
                    </button>
                    <a href="/" class="us-ghost-btn">No, keep me subscribed</a>
                </div>
            </template>

        </div>
    </main>
</template>

<style scoped>
.us {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 96px 1.25rem 4rem;
}

.us-card {
    width: 100%;
    max-width: 480px;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    background: #fffafa;
    box-shadow: 0 4px 24px rgba(229, 201, 199, 0.4);
    padding: 2.5rem 2rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    position: relative;
    overflow: hidden;
}

.us-card::before {
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
}

/* ── Icon ── */
.us-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.25rem;
}

.us-icon--done {
    background: #f0faf0;
    border: 1px solid #a8d8b0;
    color: #2d7a3a;
}

.us-icon--warn {
    background: #fdf4f3;
    border: 1px solid #e5c9c7;
    color: #8c4a50;
}

.us-icon--info {
    background: #fffbf0;
    border: 1px solid #e0c878;
    color: #8a5a00;
}

.us-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.7rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.us-body {
    font-size: 0.92rem;
    color: #6b4f4f;
    line-height: 1.65;
}

.us-body strong {
    color: #2d1a1a;
    font-weight: 600;
}

.us-note {
    font-size: 0.8rem;
    color: #9a7070;
    font-style: italic;
    line-height: 1.5;
}

.us-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    margin-top: 0.25rem;
}

.btn-rose {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 0.7rem 1.75rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
    width: 100%;
}

.btn-rose:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.us-ghost-btn {
    font-size: 0.85rem;
    color: #9a7070;
    text-decoration: underline;
    text-underline-offset: 2px;
    transition: color 0.2s;
}

.us-ghost-btn:hover {
    color: #2d1a1a;
}
</style>
