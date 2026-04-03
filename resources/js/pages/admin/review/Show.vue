<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import StarRating from '@/components/ui/coy/StarRating.vue';

interface User { id: number; name: string; email: string; }
interface Product { id: number; mpn: string; name: string; }
interface Review {
    id: number;
    user_id: number | null;
    product_id: number;
    message: string;
    rating: number;
    review_images: string[];
    admin_reply: string | null;
    status: 'pending' | 'approved' | 'rejected';
    created_at: string;
    user: User;
    product: Product;
}

const props = defineProps<{ review: Review }>();

// Status form
const statusForm = useForm({ status: props.review.status });

// Reply form
const replyForm = useForm({ admin_reply: props.review.admin_reply ?? '' });

const updateStatus = (newStatus: Review['status']) => {
    if (newStatus === props.review.status) return;
    statusForm.status = newStatus;
    statusForm.put(route('admin.reviews.update', props.review.id), { preserveScroll: true });
};

const saveReply = () => {
    replyForm.post(route('admin.reviews.reply', props.review.id), { preserveScroll: true });
};

const clearReply = () => {
    replyForm.admin_reply = '';
    replyForm.post(route('admin.reviews.reply', props.review.id), { preserveScroll: true });
};

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const statusClass = (s: string) => {
    if (s === 'approved') return 'badge--green';
    if (s === 'pending') return 'badge--amber';
    if (s === 'rejected') return 'badge--red';
    return 'badge--grey';
};

const hasReply = computed(() => !!props.review.admin_reply?.trim());
</script>

<template>
    <AdminLayout>

        <Head :title="`Review #${review.id}`" />

        <!-- Header -->
        <div class="rs-header">
            <div class="rs-header-left">
                <Link :href="route('admin.reviews.index')" class="rs-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                All Reviews
                </Link>
                <h1 class="rs-title">Review #{{ review.id }}</h1>
                <p class="rs-sub">Submitted {{ fmtDate(review.created_at) }}</p>
            </div>
            <span class="rs-badge" :class="statusClass(review.status)">{{ review.status }}</span>
        </div>

        <div class="rs-grid">

            <!-- ── Left column ── -->
            <div class="rs-left">

                <!-- Review content -->
                <div class="rs-card">
                    <h2 class="rs-card-title">Review Content</h2>

                    <!-- Stars -->
                    <div class="rs-stars-row">
                        <StarRating :rating="review.rating" :size="22" class="text-yellow-500" />
                        <span class="rs-rating-label">{{ review.rating }} / 5</span>
                    </div>

                    <!-- Message -->
                    <blockquote class="rs-message">
                        "{{ review.message }}"
                    </blockquote>

                    <!-- Images -->
                    <div v-if="review.review_images?.length" class="rs-images">
                        <h3 class="rs-images-label">Attached Photos</h3>
                        <div class="rs-image-row">
                            <img v-for="(img, i) in review.review_images" :key="i" :src="img"
                                :alt="`Review photo ${i + 1}`" class="rs-review-img" />
                        </div>
                    </div>
                </div>

                <!-- Admin reply -->
                <div class="rs-card">
                    <h2 class="rs-card-title">
                        Admin Reply
                        <span v-if="hasReply" class="rs-reply-badge">Published</span>
                        <span v-else class="rs-reply-badge rs-reply-badge--none">No reply</span>
                    </h2>

                    <p class="rs-reply-help">
                        Your reply will appear publicly beneath this review on the product page, attributed as "Chapter
                        of You".
                        {{ review.status !== 'approved' ? ' Note: the review must be approved for your reply to be
                        visible.' : '' }}
                    </p>

                    <!-- Existing reply preview -->
                    <div v-if="hasReply" class="rs-reply-preview">
                        <div class="rs-reply-preview-label">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            Chapter of You replied:
                        </div>
                        <p class="rs-reply-preview-text">{{ review.admin_reply }}</p>
                    </div>

                    <!-- Reply form -->
                    <div class="rs-reply-form">
                        <textarea v-model="replyForm.admin_reply" rows="4" class="rs-textarea"
                            :placeholder="hasReply ? 'Edit your reply...' : 'Write a reply to this customer...'"
                            maxlength="1000">
                        </textarea>
                        <div class="rs-reply-actions">
                            <span class="rs-char-count">{{ replyForm.admin_reply.length }} / 1000</span>
                            <div class="rs-reply-btns">
                                <button v-if="hasReply" @click="clearReply" :disabled="replyForm.processing"
                                    class="rs-btn rs-btn--danger">
                                    Remove Reply
                                </button>
                                <button @click="saveReply"
                                    :disabled="replyForm.processing || !replyForm.admin_reply.trim()"
                                    class="rs-btn rs-btn--primary">
                                    <svg v-if="replyForm.processing" class="rs-spinner" viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)"
                                            stroke-width="3" />
                                        <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3"
                                            stroke-linecap="round" />
                                    </svg>
                                    {{ replyForm.processing ? 'Saving...' : hasReply ? 'Update Reply' : 'Publish Reply'
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product & customer details -->
                <div class="rs-card">
                    <h2 class="rs-card-title">Product</h2>
                    <div class="rs-detail-grid">
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">Name</span>
                            <span class="rs-detail-val">{{ review.product.name }}</span>
                        </div>
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">MPN</span>
                            <span class="rs-detail-val">{{ review.product.mpn }}</span>
                        </div>
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">View Live</span>
                            <Link :href="route('products.show', review.product.id)" target="_blank" class="rs-ext-link">
                            Product Page
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="rs-card">
                    <h2 class="rs-card-title">Customer</h2>
                    <div class="rs-detail-grid">
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">Name</span>
                            <span class="rs-detail-val">{{ review.user.name }}</span>
                        </div>
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">Email</span>
                            <a :href="`mailto:${review.user.email}`" class="rs-ext-link">{{ review.user.email }}</a>
                        </div>
                        <div class="rs-detail-item">
                            <span class="rs-detail-label">Account</span>
                            <Link :href="route('admin.users.show', review.user.id)" class="rs-ext-link">
                            View Profile
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ── Right column: status actions ── -->
            <aside class="rs-right">
                <div class="rs-actions-card">
                    <h2 class="rs-actions-title">Moderation</h2>

                    <div class="rs-status-row">
                        <span class="rs-status-label">Status</span>
                        <span class="rs-badge" :class="statusClass(review.status)">{{ review.status }}</span>
                    </div>

                    <div class="rs-action-btns">
                        <button @click="updateStatus('approved')"
                            :disabled="statusForm.processing || review.status === 'approved'"
                            class="rs-btn rs-btn--approve" :class="{ 'rs-btn--current': review.status === 'approved' }">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            {{ review.status === 'approved' ? 'Approved' : 'Approve' }}
                        </button>

                        <button @click="updateStatus('pending')"
                            :disabled="statusForm.processing || review.status === 'pending'"
                            class="rs-btn rs-btn--pending" :class="{ 'rs-btn--current': review.status === 'pending' }">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ review.status === 'pending' ? 'Already Pending' : 'Set Pending' }}
                        </button>

                        <button @click="updateStatus('rejected')"
                            :disabled="statusForm.processing || review.status === 'rejected'"
                            class="rs-btn rs-btn--reject" :class="{ 'rs-btn--current': review.status === 'rejected' }">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                            {{ review.status === 'rejected' ? 'Rejected' : 'Reject' }}
                        </button>
                    </div>

                    <p class="rs-moderation-note">
                        Only <strong>approved</strong> reviews are shown publicly on the product page.
                    </p>
                </div>
            </aside>

        </div>
    </AdminLayout>
</template>

<style scoped>
/* ── Header ── */
.rs-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 2px solid var(--copy);
}

.rs-header-left {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.rs-back {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--copy-light);
    text-decoration: none;
    transition: color 0.15s;
    margin-bottom: 0.25rem;
}

.rs-back:hover {
    color: var(--copy);
}

.rs-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--copy);
}

.rs-sub {
    font-size: 0.82rem;
    color: var(--copy-light);
}

/* ── Status badge ── */
.rs-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.85rem;
    border-radius: 999px;
    border: 1px solid transparent;
}

.badge--green {
    background: #f0faf0;
    color: #2d7a3a;
    border-color: #a8d8b0;
}

.badge--amber {
    background: #fffbf0;
    color: #8a5a00;
    border-color: #e0c878;
}

.badge--red {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
}

.badge--grey {
    background: #f5f5f5;
    color: #555;
    border-color: #ccc;
}

/* ── Grid ── */
.rs-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .rs-grid {
        grid-template-columns: 1fr;
    }
}

.rs-left {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.rs-right {
    position: sticky;
    top: 80px;
}

/* ── Cards ── */
.rs-card {
    border: 2px solid var(--copy);
    border-radius: 12px;
    background: var(--foreground);
    padding: 1.5rem;
}

.rs-card-title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--copy);
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
}

/* ── Review content ── */
.rs-stars-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.rs-rating-label {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--copy-light);
}

.rs-message {
    font-size: 0.97rem;
    line-height: 1.7;
    color: var(--copy);
    border-left: 3px solid var(--primary, #8c4a50);
    padding-left: 1rem;
    margin: 0 0 1rem;
    font-style: italic;
}

.rs-images {
    margin-top: 0.5rem;
}

.rs-images-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--copy-lighter);
    margin-bottom: 0.5rem;
}

.rs-image-row {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.rs-review-img {
    width: 72px;
    height: 72px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--border, #e5e7eb);
    cursor: pointer;
    transition: opacity 0.15s;
}

.rs-review-img:hover {
    opacity: 0.8;
}

/* ── Reply section ── */
.rs-reply-badge {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.12rem 0.5rem;
    border-radius: 999px;
    background: #f0faf0;
    color: #2d7a3a;
    border: 1px solid #a8d8b0;
}

.rs-reply-badge--none {
    background: #f5f5f5;
    color: var(--copy-lighter);
    border-color: #ccc;
}

.rs-reply-help {
    font-size: 0.82rem;
    color: var(--copy-light);
    line-height: 1.55;
    margin-bottom: 1rem;
    font-style: italic;
}

.rs-reply-preview {
    background: var(--secondary-light, #f9fafb);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 10px;
    padding: 0.85rem 1rem;
    margin-bottom: 1rem;
}

.rs-reply-preview-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--copy-lighter);
    margin-bottom: 0.4rem;
}

.rs-reply-preview-text {
    font-size: 0.9rem;
    color: var(--copy);
    line-height: 1.6;
}

.rs-reply-form {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.rs-textarea {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border: 2px solid color-mix(in srgb, var(--copy-light) 40%, transparent);
    border-radius: 10px;
    background: var(--background);
    color: var(--copy);
    font-family: inherit;
    font-size: 0.92rem;
    line-height: 1.6;
    resize: vertical;
    outline: none;
    transition: border-color 0.2s;
}

.rs-textarea:focus {
    border-color: var(--primary, #8c4a50);
}

.rs-reply-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.rs-char-count {
    font-size: 0.75rem;
    color: var(--copy-lighter);
}

.rs-reply-btns {
    display: flex;
    gap: 0.5rem;
}

/* ── Detail grid ── */
.rs-detail-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.rs-detail-item {
    display: flex;
    align-items: baseline;
    gap: 1rem;
}

.rs-detail-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--copy-lighter);
    min-width: 80px;
    flex-shrink: 0;
}

.rs-detail-val {
    font-size: 0.9rem;
    color: var(--copy);
}

.rs-ext-link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--primary, #8c4a50);
    text-decoration: none;
    transition: opacity 0.15s;
}

.rs-ext-link:hover {
    opacity: 0.75;
}

/* ── Actions card ── */
.rs-actions-card {
    border: 2px solid var(--copy);
    border-radius: 12px;
    background: var(--foreground);
    padding: 1.5rem;
}

.rs-actions-title {
    font-size: 1rem;
    font-weight: 800;
    color: var(--copy);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid color-mix(in srgb, var(--copy-light) 30%, transparent);
}

.rs-status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.rs-status-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--copy-light);
}

.rs-action-btns {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

/* ── Buttons ── */
.rs-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    border: 2px solid var(--copy);
    font-family: inherit;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}

.rs-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.rs-btn:not(:disabled):hover {
    transform: translateY(-1px);
}

.rs-btn--primary {
    background: var(--primary, #8c4a50);
    color: #fff;
    border-color: var(--primary, #8c4a50);
}

.rs-btn--danger {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
}

.rs-btn--approve {
    background: #f0faf0;
    color: #2d7a3a;
    border-color: #a8d8b0;
    width: 100%;
}

.rs-btn--approve:not(.rs-btn--current):not(:disabled):hover {
    background: #2d7a3a;
    color: #fff;
}

.rs-btn--pending {
    background: #fffbf0;
    color: #8a5a00;
    border-color: #e0c878;
    width: 100%;
}

.rs-btn--reject {
    background: #fff5f5;
    color: #8c2a2a;
    border-color: #e8a8a8;
    width: 100%;
}

.rs-btn--reject:not(.rs-btn--current):not(:disabled):hover {
    background: #8c2a2a;
    color: #fff;
}

.rs-btn--current {
    opacity: 0.5;
    cursor: not-allowed;
}

.rs-moderation-note {
    font-size: 0.78rem;
    color: var(--copy-lighter);
    line-height: 1.5;
    margin-top: 1rem;
    font-style: italic;
}

/* ── Spinner ── */
.rs-spinner {
    width: 14px;
    height: 14px;
    animation: rs-spin 0.8s linear infinite;
}

@keyframes rs-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
