<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    amounts: number[];
}>();

const seo = useSeoHead({
    title: 'Gift Vouchers',
    description: 'Give the gift of self-care. Purchase a Chapter of You gift voucher, available as an e-voucher or physical voucher.',
    canonical: '/gift-vouchers',
});

// ── Form state ─────────────────────────────────────────────────────────────
const selectedAmount = ref<number | null>(null);
const customAmount = ref('');
const deliveryType = ref<'email' | 'physical'>('email');
const recipientName = ref('');
const recipientEmail = ref('');
const personalMessage = ref('');
const senderName = ref('');
const senderEmail = ref('');

const errors = ref<Record<string, string>>({});
const submitting = ref(false);
const submitted = ref(false);

const finalAmount = computed(() => {
    if (selectedAmount.value) return selectedAmount.value;
    const n = parseFloat(customAmount.value);
    return isNaN(n) ? null : n;
});

const fmt = (n: number) => `£${n.toFixed(2)}`;

function selectAmount(a: number) {
    selectedAmount.value = a;
    customAmount.value = '';
}

function onCustomAmount() {
    selectedAmount.value = null;
}

function validate(): boolean {
    errors.value = {};
    if (!finalAmount.value || finalAmount.value < 5)
        errors.value.amount = 'Please select or enter a valid amount (minimum £5).';
    if (finalAmount.value && finalAmount.value > 500)
        errors.value.amount = 'Maximum gift voucher value is £500.';
    if (!recipientName.value.trim())
        errors.value.recipientName = 'Recipient name is required.';
    if (deliveryType.value === 'email' && !recipientEmail.value.trim())
        errors.value.recipientEmail = 'Recipient email is required for e-vouchers.';
    if (!senderName.value.trim())
        errors.value.senderName = 'Your name is required.';
    if (!senderEmail.value.trim())
        errors.value.senderEmail = 'Your email is required for the order confirmation.';
    return Object.keys(errors.value).length === 0;
}

async function submit() {
    if (!validate() || submitting.value) return;
    submitting.value = true;
    try {
        const res = await axios.post(route('gift-vouchers.checkout'), {
            amount: finalAmount.value,
            delivery_type: deliveryType.value,
            recipient_name: recipientName.value,
            recipient_email: recipientEmail.value,
            personal_message: personalMessage.value,
            sender_name: senderName.value,
            sender_email: senderEmail.value,
        });
        // Redirect to checkout with gift voucher in cart
        if (res.data?.redirect) {
            window.location.href = res.data.redirect;
        }
    } catch (e: any) {
        const data = e?.response?.data;
        if (data?.errors) {
            // Laravel validation errors
            Object.entries(data.errors).forEach(([k, v]: any) => {
                errors.value[k] = Array.isArray(v) ? v[0] : v;
            });
        } else {
            errors.value.general = data?.message ?? 'Something went wrong. Please try again.';
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="gv">
        <div class="gv-wrap">

            <!-- Header -->
            <header class="gv-header">
                <p class="gv-eyebrow">Chapter of You</p>
                <h1 class="gv-title">Gift <em>Vouchers</em></h1>
                <div class="gv-rule">
                    <span></span><span class="gv-petal">✿</span><span></span>
                </div>
                <p class="gv-intro">
                    Give the gift of self-care. My gift vouchers can be used on
                    any product in the collection and are valid for one year from purchase.
                </p>
            </header>

            <div class="gv-grid">

                <!-- ── Left: form ── -->
                <div class="gv-form-col">

                    <!-- Step 1: Amount -->
                    <div class="gv-card">
                        <h2 class="gv-card-title">
                            <span class="gv-step">1</span>
                            Choose an amount
                        </h2>
                        <div class="gv-amounts">
                            <button v-for="a in amounts" :key="a" type="button" class="gv-amount-btn"
                                :class="{ 'gv-amount-btn--active': selectedAmount === a }" @click="selectAmount(a)">
                                {{ fmt(a) }}
                            </button>
                        </div>
                        <div class="gv-custom-wrap">
                            <label class="gv-label">Or enter a custom amount</label>
                            <div class="gv-prefix-wrap">
                                <span class="gv-prefix">£</span>
                                <input v-model="customAmount" type="number" min="5" max="500" step="1"
                                    class="gv-input gv-input--prefixed" placeholder="0.00" @input="onCustomAmount" />
                            </div>
                        </div>
                        <p v-if="errors.amount" class="gv-err">{{ errors.amount }}</p>
                    </div>

                    <!-- Step 2: Delivery type -->
                    <div class="gv-card">
                        <h2 class="gv-card-title">
                            <span class="gv-step">2</span>
                            Delivery type
                        </h2>
                        <div class="gv-delivery-options">
                            <button type="button" class="gv-delivery-opt"
                                :class="{ 'gv-delivery-opt--active': deliveryType === 'email' }"
                                @click="deliveryType = 'email'">
                                <div class="gv-delivery-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                        <polyline points="22,6 12,13 2,6" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="gv-delivery-name">E-Voucher</p>
                                    <p class="gv-delivery-desc">Sent instantly by email to the recipient</p>
                                </div>
                                <span v-if="deliveryType === 'email'" class="gv-delivery-check">✓</span>
                            </button>
                            <button type="button" class="gv-delivery-opt"
                                :class="{ 'gv-delivery-opt--active': deliveryType === 'physical' }"
                                @click="deliveryType = 'physical'">
                                <div class="gv-delivery-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="gv-delivery-name">Physical Voucher</p>
                                    <p class="gv-delivery-desc">Printed and posted to the recipient's address</p>
                                </div>
                                <span v-if="deliveryType === 'physical'" class="gv-delivery-check">✓</span>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Recipient details -->
                    <div class="gv-card">
                        <h2 class="gv-card-title">
                            <span class="gv-step">3</span>
                            Recipient details
                        </h2>
                        <div class="gv-field">
                            <label class="gv-label">Recipient's name <span class="gv-req">*</span></label>
                            <input v-model="recipientName" type="text" class="gv-input"
                                :class="{ 'gv-input--err': errors.recipientName }" placeholder="Jane Smith" />
                            <p v-if="errors.recipientName" class="gv-err">{{ errors.recipientName }}</p>
                        </div>
                        <div v-if="deliveryType === 'email'" class="gv-field">
                            <label class="gv-label">Recipient's email address <span class="gv-req">*</span></label>
                            <input v-model="recipientEmail" type="email" class="gv-input"
                                :class="{ 'gv-input--err': errors.recipientEmail }" placeholder="jane@example.com" />
                            <p v-if="errors.recipientEmail" class="gv-err">{{ errors.recipientEmail }}</p>
                        </div>
                        <div class="gv-field">
                            <label class="gv-label">Personal message <span class="gv-opt">(optional)</span></label>
                            <textarea v-model="personalMessage" rows="3" class="gv-input gv-textarea"
                                placeholder="Write a short message to include with the voucher…"
                                maxlength="500"></textarea>
                            <p class="gv-char-count">{{ personalMessage.length }}/500</p>
                            <div class="gv-message-hint">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9" />
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                </svg>
                                <span>
                                    If left blank, I'll include the message below for you:<br />
                                    <em>"A little moment of self-care, chosen just for you 🤍"</em><br />
                                    <span class="gv-message-hint-note">Messages are handwritten for a personal
                                        touch</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Your details -->
                    <div class="gv-card">
                        <h2 class="gv-card-title">
                            <span class="gv-step">4</span>
                            Your details
                        </h2>
                        <div class="gv-field-row">
                            <div class="gv-field">
                                <label class="gv-label">Your name <span class="gv-req">*</span></label>
                                <input v-model="senderName" type="text" class="gv-input"
                                    :class="{ 'gv-input--err': errors.senderName }" placeholder="Your name" />
                                <p v-if="errors.senderName" class="gv-err">{{ errors.senderName }}</p>
                            </div>
                            <div class="gv-field">
                                <label class="gv-label">Your email <span class="gv-req">*</span></label>
                                <input v-model="senderEmail" type="email" class="gv-input"
                                    :class="{ 'gv-input--err': errors.senderEmail }"
                                    placeholder="For your order confirmation" />
                                <p v-if="errors.senderEmail" class="gv-err">{{ errors.senderEmail }}</p>
                            </div>
                        </div>
                    </div>

                    <p v-if="errors.general" class="gv-err gv-err--general">{{ errors.general }}</p>

                    <button @click="submit" :disabled="submitting || !finalAmount" class="gv-submit-btn">
                        <svg v-if="submitting" class="gv-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ submitting ? 'Processing…' : finalAmount ? `Purchase ${fmt(finalAmount)} Gift Voucher` :
                            'Select an amount to continue' }}
                    </button>
                </div>

                <!-- ── Right: preview ── -->
                <aside class="gv-preview-col">
                    <div class="gv-preview-card">
                        <p class="gv-preview-label">Preview</p>
                        <div class="gv-voucher-preview">
                            <p class="gv-preview-brand">Chapter of You</p>
                            <div class="gv-preview-petal" aria-hidden="true">✿</div>
                            <p class="gv-preview-amount">
                                {{ finalAmount ? fmt(finalAmount) : '£—' }}
                            </p>
                            <p class="gv-preview-gift-label">Gift Voucher</p>
                            <div class="gv-preview-code-box">
                                <span class="gv-preview-code-hint">Your unique code will appear here</span>
                            </div>
                            <p class="gv-preview-terms">
                                Single use · All products · Valid for 1 year
                            </p>
                        </div>

                        <div v-if="recipientName || personalMessage" class="gv-preview-message">
                            <p class="gv-preview-to">
                                For: <strong>{{ recipientName || '—' }}</strong>
                            </p>
                            <p v-if="personalMessage" class="gv-preview-msg-text">
                                "{{ personalMessage }}"
                            </p>
                        </div>

                        <div class="gv-preview-info">
                            <div class="gv-preview-info-row">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                    <polyline points="22,6 12,13 2,6" />
                                </svg>
                                <span>{{ deliveryType === 'email' ? 'Sent instantly by email' : 'Posted to recipient'
                                    }}</span>
                            </div>
                            <div class="gv-preview-info-row">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                                <span>Valid for 1 year from purchase</span>
                            </div>
                            <div class="gv-preview-info-row">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                </svg>
                                <span>Redeemable on all products</span>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.gv {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.gv-wrap {
    max-width: 1060px;
    margin: 0 auto;
    padding: 3.5rem 1.25rem 5rem;
}

/* Header */
.gv-header {
    text-align: center;
    margin-bottom: 3rem;
}

.gv-eyebrow {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 0.75rem;
}

.gv-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(2.2rem, 6vw, 3.5rem);
    font-weight: 400;
    font-style: normal;
    color: #2d1a1a;
    margin-bottom: 1.25rem;
    line-height: 1.1;
}

.gv-title em {
    font-style: italic;
    color: #8c4a50;
}

.gv-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.gv-rule span:not(.gv-petal) {
    display: block;
    width: 60px;
    height: 1px;
    background: #e5c9c7;
}

.gv-petal {
    font-size: 0.85rem;
    color: #c9a4a4;
}

.gv-intro {
    font-size: 0.95rem;
    color: #6b4f4f;
    line-height: 1.7;
    max-width: 500px;
    margin: 0 auto;
}

/* Grid */
.gv-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 860px) {
    .gv-grid {
        grid-template-columns: 1fr;
    }
}

.gv-form-col {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

@media (min-width: 860px) {
    .gv-preview-col {
        position: sticky;
        top: 84px;
    }
}

/* Cards */
.gv-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.gv-card::after {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #e5c9c7;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.gv-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #2d1a1a;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f0dcd8;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.gv-step {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Amounts */
.gv-amounts {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.gv-amount-btn {
    padding: 0.55rem 1.1rem;
    border: 1px solid #e5c9c7;
    border-radius: 999px;
    background: #fdf4f3;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}

.gv-amount-btn:hover {
    border-color: #8c4a50;
    color: #8c4a50;
    background: #fff5f5;
}

.gv-amount-btn--active {
    border-color: #8c4a50;
    background: #8c4a50;
    color: #fff;
}

.gv-custom-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.gv-prefix-wrap {
    display: flex;
    align-items: center;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    overflow: hidden;
    background: #fdf4f3;
    max-width: 180px;
}

.gv-prefix {
    padding: 0.68rem 0.75rem;
    background: #f5e8e6;
    border-right: 1px solid #e5c9c7;
    font-weight: 600;
    color: #8c4a50;
    font-size: 0.92rem;
}

.gv-input--prefixed {
    border: none !important;
    border-radius: 0 !important;
    background: transparent !important;
    flex: 1;
}

/* Delivery options */
.gv-delivery-options {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.gv-delivery-opt {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.1rem;
    border: 1px solid #e5c9c7;
    border-radius: 12px;
    background: #fdf4f3;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s;
    font-family: 'Nunito', sans-serif;
}

.gv-delivery-opt:hover {
    border-color: #c9a4a4;
    background: #faeaea;
}

.gv-delivery-opt--active {
    border-color: #8c4a50;
    background: #fff5f5;
    box-shadow: 0 0 0 2px rgba(140, 74, 80, 0.08);
}

.gv-delivery-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(140, 74, 80, 0.07);
    border: 1px solid #e5c9c7;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8c4a50;
    flex-shrink: 0;
}

.gv-delivery-name {
    font-size: 0.9rem;
    font-weight: 700;
    color: #2d1a1a;
    margin-bottom: 0.15rem;
}

.gv-delivery-desc {
    font-size: 0.78rem;
    color: #6b4f4f;
}

.gv-delivery-check {
    margin-left: auto;
    color: #8c4a50;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

/* Fields */
.gv-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 540px) {
    .gv-field-row {
        grid-template-columns: 1fr;
    }
}

.gv-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.gv-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b4f4f;
}

.gv-req {
    color: #8c4a50;
}

.gv-opt {
    font-weight: 400;
    text-transform: none;
    font-style: italic;
    color: #9a7070;
}

.gv-input {
    padding: 0.68rem 0.9rem;
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

.gv-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.gv-input--err {
    border-color: #c84040;
}

.gv-textarea {
    resize: vertical;
    min-height: 80px;
}

.gv-char-count {
    font-size: 0.72rem;
    color: #c9a4a4;
    text-align: right;
}

.gv-err {
    font-size: 0.78rem;
    color: #c84040;
}

.gv-err--general {
    padding: 0.75rem 1rem;
    background: #fff5f5;
    border: 1px solid #e8a8a8;
    border-radius: 10px;
}

/* Submit */
.gv-submit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.9rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(168, 80, 88, 0.25);
    transition: transform 0.2s, box-shadow 0.2s;
}

.gv-submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(168, 80, 88, 0.32);
}

.gv-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.gv-spinner {
    width: 16px;
    height: 16px;
    animation: gv-spin 0.8s linear infinite;
}

@keyframes gv-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* Preview card */
.gv-preview-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.gv-preview-label {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #c9a4a4;
}

.gv-voucher-preview {
    background: linear-gradient(135deg, #2d1a1a 0%, #4a2828 100%);
    border-radius: 14px;
    padding: 1.75rem 1.25rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.gv-preview-brand {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem;
    font-style: italic;
    color: #e5c9c7;
    margin-bottom: 0.25rem;
}

.gv-preview-petal {
    font-size: 1.2rem;
    color: #c9a4a4;
    opacity: 0.5;
    margin-bottom: 0.5rem;
}

.gv-preview-amount {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2.5rem;
    font-weight: 500;
    color: #fffafa;
    line-height: 1;
    margin-bottom: 0.35rem;
}

.gv-preview-gift-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #c9a4a4;
    margin-bottom: 1rem;
}

.gv-preview-code-box {
    background: rgba(255, 250, 250, 0.07);
    border: 1px dashed rgba(201, 168, 76, 0.4);
    border-radius: 6px;
    padding: 0.6rem;
    margin-bottom: 0.75rem;
}

.gv-preview-code-hint {
    font-size: 0.72rem;
    font-style: italic;
    color: rgba(255, 250, 250, 0.4);
}

.gv-preview-terms {
    font-size: 0.65rem;
    color: rgba(255, 250, 250, 0.3);
    letter-spacing: 0.04em;
}

.gv-preview-message {
    background: #fdf4f3;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    padding: 0.85rem 1rem;
}

.gv-preview-to {
    font-size: 0.82rem;
    color: #6b4f4f;
    margin-bottom: 0.4rem;
}

.gv-preview-msg-text {
    font-size: 0.85rem;
    font-style: italic;
    color: #2d1a1a;
    line-height: 1.55;
}

.gv-preview-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.gv-preview-info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: #6b4f4f;
}

.gv-preview-info-row svg {
    flex-shrink: 0;
    color: #8c4a50;
}

.gv-message-hint {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding: 0.75rem 0.9rem;
    background: rgba(140, 74, 80, 0.04);
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    font-size: 0.82rem;
    color: #6b4f4f;
    line-height: 1.6;
}

.gv-message-hint svg {
    flex-shrink: 0;
    color: #8c4a50;
    margin-top: 2px;
}

.gv-message-hint em {
    color: #2d1a1a;
    font-style: italic;
}

.gv-message-hint-note {
    font-size: 0.76rem;
    color: #9a7070;
    font-style: italic;
}
</style>
