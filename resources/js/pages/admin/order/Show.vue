<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, reactive, watch, onMounted } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

interface User { id: number; name: string; email: string; }
interface Product { id: number; mpn: string; name: string; cost: number; }
interface OrderItem {
    id: number; product_id: number; product: Product;
    quantity: number; product_cost: number; product_total: number;
}
interface Order {
    id: number; user_id: number | null; user: User | null;
    payment_intent_id: string; payment_type: string;
    first_name: string; last_name: string; email: string; telephone: string | null;
    cost_total: number; shipping_total: number; voucher_discount: number;
    tax_total: number; grand_total: number;
    billing_line_1: string; billing_line_2: string | null;
    billing_city: string; billing_county: string | null; billing_postcode: string;
    shipping_line_1: string; shipping_line_2: string | null;
    shipping_city: string; shipping_county: string | null; shipping_postcode: string;
    status: string; created_at: string; items: OrderItem[];
}

const props = defineProps<{
    order: Order;
    statuses: Record<string, string>;
    giftVoucherOrder: {
        id: number;
        delivery_type: 'email' | 'physical';
        amount: string;
        recipient_name: string;
        recipient_email: string | null;
        personal_message: string | null;
        fulfilled_at: string | null;
        voucher: { code: string; valid_until: string; } | null;
    } | null;
}>();

const { fmtCurrency } = useAdmin();

const page = usePage();
const flash = computed(() => (page.props.flash as any) ?? {});

const selectedStatus = ref(props.order.status);
const updatingStatus = ref(false);
const sendingDispatch = ref(false);
const sendingConfirmation = ref(false);
const trackingUrl = ref('');

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const orderId = computed(() => `COY-${props.order.id}`);

const statusBadgeClass = computed(() => {
    const map: Record<string, string> = {
        successful: 'adm-badge--on',
        processing: 'adm-badge--lav',
        'not started': 'adm-badge--warn',
        cancelled: 'adm-badge--red',
        failed: 'adm-badge--red',
        refunded: 'adm-badge--off',
    };
    return map[props.order.status] ?? 'adm-badge--off';
});

function updateStatus() {
    if (selectedStatus.value === props.order.status) return;
    updatingStatus.value = true;
    router.post(route('admin.orders.status', props.order.id), {
        status: selectedStatus.value,
        tracking_url: trackingUrl.value || null,
    }, {
        preserveScroll: true,
        onFinish: () => { updatingStatus.value = false; },
    });
}

function sendDispatch() {
    if (!confirm(`Send dispatch email to ${props.order.email}?`)) return;
    sendingDispatch.value = true;
    router.post(route('admin.orders.dispatch-email', props.order.id), {
        tracking_url: trackingUrl.value || null,
    }, {
        preserveScroll: true,
        onFinish: () => { sendingDispatch.value = false; },
    });
}

function resendConfirmation() {
    if (!confirm(`Resend confirmation email to ${props.order.email}?`)) return;
    sendingConfirmation.value = true;
    router.post(route('admin.orders.resend-confirmation', props.order.id), {}, {
        preserveScroll: true,
        onFinish: () => { sendingConfirmation.value = false; },
    });
}

// ── Order Checklist ────────────────────────────────────────────────────────
// Keyed by order ID so each order has independent state.
// Persisted to localStorage — no backend required.

interface ChecklistGroup {
    id: string;
    label: string;
    colour: 'blush' | 'lav' | 'sage' | 'peach';
    items: { id: string; label: string; link?: string }[];
}

const CHECKLIST: ChecklistGroup[] = [
    {
        id: 'equipment',
        label: 'Equipment Check',
        colour: 'lav',
        items: [
            { id: 'eq-apron', label: 'Get apron' },
            { id: 'eq-bottles', label: 'Get diffuser bottle(s) required' },
            { id: 'eq-caps', label: 'Get diffuser bottle cap(s) required' },
            { id: 'eq-boxes', label: 'Make up diffuser box(es)' },
            { id: 'eq-oils', label: 'Get required oil(s)' },
            { id: 'eq-base', label: 'Get base oil' },
            { id: 'eq-alcohol', label: 'Get rubbing alcohol' },
            { id: 'eq-reeds', label: 'Get reed sticks (6 per diffuser)' },
            { id: 'eq-gloves', label: 'Get gloves' },
            { id: 'eq-tools', label: 'Get jugs / beakers / stirrers / pipettes / funnel' },
            { id: 'eq-scales', label: 'Get scales' },
        ],
    },
    {
        id: 'making',
        label: 'Diffuser Making',
        colour: 'blush',
        items: [
            { id: 'mk-batch', label: 'Create Batch Sheet (1 per product) in Batch Sheets' },
        ],
    },
    {
        id: 'packaging',
        label: 'Packaging',
        colour: 'sage',
        items: [
            { id: 'pk-shipbox', label: 'Make up shipping box(es)' },
            { id: 'pk-clp', label: 'Print CLP label for diffuser' },
            { id: 'pk-scent', label: 'Print scent label for diffuser' },
            { id: 'pk-picto', label: 'Get pictogram stickers for CLP label' },
            { id: 'pk-warning', label: 'Get tactical warning label for diffuser' },
            { id: 'pk-vellum', label: 'Get vellum paper to line shipping box' },
            { id: 'pk-shred', label: 'Get shredded paper' },
            { id: 'pk-cards', label: 'Get reed diffuser care card / business card / thank you card' },
            { id: 'pk-sweets', label: 'Get sweets / chocolates' },
            { id: 'pk-sticker-lg', label: 'Get large logo sticker for front of diffuser box' },
            { id: 'pk-sticker-sm', label: 'Get small logo sticker for back of diffuser box' },
            { id: 'pk-bubble', label: 'Get bubble wrap for diffuser box' },
        ],
    },
    {
        id: 'sending',
        label: 'Sending Package',
        colour: 'peach',
        items: [
            { id: 'sn-tape', label: 'Get fragile tape' },
            { id: 'sn-label', label: 'Print shipping label from Royal Mail' },
            { id: 'sn-postoffice', label: 'Take package to post office' },
            { id: 'sn-dispatch', label: 'Send customer dispatch email' },
        ],
    },
];

const STORAGE_KEY = `order-checklist-${props.order.id}`;

// checked: Set of item IDs that are ticked
const checked = reactive(new Set<string>());
// collapsed: Set of group IDs that are folded shut
const collapsed = reactive(new Set<string>());

// Total / completed counts
const totalItems = computed(() => CHECKLIST.reduce((n, g) => n + g.items.length, 0));
const completedItems = computed(() => checked.size);
const progressPct = computed(() => Math.round((completedItems.value / totalItems.value) * 100));
const allDone = computed(() => completedItems.value === totalItems.value);

function groupCompleted(group: ChecklistGroup) {
    return group.items.every(i => checked.has(i.id));
}
function groupProgress(group: ChecklistGroup) {
    return group.items.filter(i => checked.has(i.id)).length;
}

function toggle(itemId: string) {
    checked.has(itemId) ? checked.delete(itemId) : checked.add(itemId);
    persist();
}
function toggleGroup(group: ChecklistGroup) {
    const allChecked = groupCompleted(group);
    group.items.forEach(i => allChecked ? checked.delete(i.id) : checked.add(i.id));
    persist();
}
function toggleCollapse(groupId: string) {
    collapsed.has(groupId) ? collapsed.delete(groupId) : collapsed.add(groupId);
    // Don't persist collapse state — it resets on each visit intentionally
}
function resetChecklist() {
    if (!confirm('Reset checklist for this order? All ticks will be cleared.')) return;
    checked.clear();
    persist();
}

function persist() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify([...checked]));
}
function load() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) {
            const ids: string[] = JSON.parse(raw);
            ids.forEach(id => checked.add(id));
        }
    } catch { /* ignore */ }
}

onMounted(load);

const vatRegistered = computed(() => !!(usePage().props.vatRegistered));
</script>

<template>
    <AdminLayout>

        <Head :title="`Order #${orderId} — Admin`" />

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5" />
            </svg>
            {{ flash.success }}
        </div>

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.orders.index')" class="adm-breadcrumb a">Orders</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ orderId }}</span>
                </div>
                <h1 class="adm-title">Order {{ orderId }}</h1>
                <p class="adm-sub">Placed {{ fmtDate(order.created_at) }}</p>
            </div>
            <span class="adm-badge" :class="statusBadgeClass" style="font-size:0.82rem; padding:0.3rem 0.9rem">
                {{ order.status }}
            </span>
        </div>

        <!-- Grid -->
        <div class="os-layout">

            <!-- ── Left / Main ── -->
            <div class="os-main">

                <!-- Items -->
                <section class="adm-card adm-card--flush">
                    <h2 class="os-section-title">Items ({{ order.items.length }})</h2>

                    <div v-for="item in order.items" :key="item.id" class="os-item-row"
                        :class="{ 'os-item-row--gv': item.product.mpn === 'GIFT-VOUCHER' }">
                        <div class="os-item-info">
                            <p class="os-item-name">
                                <span v-if="item.product.mpn === 'GIFT-VOUCHER'">🎁 </span>
                                {{ item.product.name }}
                            </p>
                            <p class="os-item-meta">
                                MPN: {{ item.product.mpn }}
                                <span class="os-sep">·</span>
                                Unit: {{ fmtCurrency(item.product_cost) }}
                            </p>
                            <!-- Gift voucher details -->
                            <template v-if="item.product.mpn === 'GIFT-VOUCHER' && giftVoucherOrder">
                                <p class="os-item-meta" style="margin-top:4px; color: #8a6a5a;">
                                    For: <strong>{{ giftVoucherOrder.recipient_name }}</strong>
                                    <span class="os-sep">·</span>
                                    {{ giftVoucherOrder.delivery_type === 'email' ? 'E-Voucher' : 'Physical Voucher' }}
                                    <template v-if="giftVoucherOrder.recipient_email">
                                        <span class="os-sep">·</span>
                                        {{ giftVoucherOrder.recipient_email }}
                                    </template>
                                </p>
                                <p class="os-item-meta" style="margin-top:2px;">
                                    Code: <span style="font-family:monospace; font-weight:700;">
                                        {{ giftVoucherOrder.voucher?.code ?? 'Pending' }}
                                    </span>
                                    <span class="os-sep">·</span>
                                    <span :style="giftVoucherOrder.fulfilled_at ? 'color:#2d7a3a' : 'color:#a86414'">
                                        {{ giftVoucherOrder.fulfilled_at ? 'Fulfilled' : 'Awaiting fulfilment' }}
                                    </span>
                                </p>
                            </template>
                        </div>
                        <div class="os-item-right">
                            <p class="os-item-price">{{ fmtCurrency(item.product_total) }}</p>
                            <p class="os-item-qty">Qty: {{ item.quantity }}</p>
                        </div>
                    </div>
                </section>

                <!-- ── Order Checklist ── -->
                <section class="adm-card adm-card--flush">

                    <!-- Checklist header -->
                    <div class="cl-header">
                        <div class="cl-header-left">
                            <h2 class="cl-title">Production Checklist</h2>
                            <span class="cl-progress-pill" :class="allDone ? 'cl-progress-pill--done' : ''">
                                {{ completedItems }} / {{ totalItems }}
                            </span>
                        </div>
                        <button v-if="completedItems > 0" @click="resetChecklist" class="cl-reset-btn"
                            title="Reset checklist">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                <path d="M3 3v5h5" />
                            </svg>
                            Reset
                        </button>
                    </div>

                    <!-- Progress bar -->
                    <div class="cl-bar-track">
                        <div class="cl-bar-fill" :class="allDone ? 'cl-bar-fill--done' : ''"
                            :style="{ width: progressPct + '%' }">
                        </div>
                    </div>

                    <!-- All done banner -->
                    <div v-if="allDone" class="cl-done-banner">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                        Order complete — all steps done! ✦
                    </div>

                    <!-- Groups -->
                    <div v-for="group in CHECKLIST" :key="group.id" class="cl-group">

                        <!-- Group header -->
                        <button class="cl-group-header" :class="`cl-group-header--${group.colour}`"
                            @click="toggleCollapse(group.id)">
                            <div class="cl-group-left">
                                <span class="cl-group-dot" :class="`cl-group-dot--${group.colour}`"></span>
                                <span class="cl-group-label">{{ group.label }}</span>
                                <span class="cl-group-count"
                                    :class="groupCompleted(group) ? 'cl-group-count--done' : ''">
                                    {{ groupProgress(group) }}/{{ group.items.length }}
                                </span>
                            </div>
                            <div class="cl-group-right">
                                <button class="cl-group-check-all"
                                    :class="groupCompleted(group) ? 'cl-group-check-all--active' : ''"
                                    @click.stop="toggleGroup(group)"
                                    :title="groupCompleted(group) ? 'Uncheck all' : 'Check all'">
                                    {{ groupCompleted(group) ? 'Uncheck all' : 'Check all' }}
                                </button>
                                <svg class="cl-chevron" :class="{ 'cl-chevron--open': !collapsed.has(group.id) }"
                                    width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </div>
                        </button>

                        <!-- Group items -->
                        <div v-if="!collapsed.has(group.id)" class="cl-items">
                            <label v-for="item in group.items" :key="item.id" class="cl-item"
                                :class="{ 'cl-item--checked': checked.has(item.id) }">
                                <span class="cl-checkbox-wrap" @click.prevent="toggle(item.id)">
                                    <svg v-if="checked.has(item.id)" width="11" height="11" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </span>
                                <span class="cl-item-label" @click="toggle(item.id)">{{ item.label }}</span>
                            </label>
                        </div>

                    </div>
                </section>

                <!-- Customer -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Customer</h2>
                    <div class="os-dl">
                        <div class="os-dl-item">
                            <dt class="os-dt">Name</dt>
                            <dd class="os-dd">{{ order.first_name }} {{ order.last_name }}</dd>
                        </div>
                        <div class="os-dl-item">
                            <dt class="os-dt">Email</dt>
                            <dd class="os-dd">
                                <a :href="`mailto:${order.email}`" class="os-link">{{ order.email }}</a>
                            </dd>
                        </div>
                        <div class="os-dl-item">
                            <dt class="os-dt">Phone</dt>
                            <dd class="os-dd">{{ order.telephone || '—' }}</dd>
                        </div>
                        <div class="os-dl-item">
                            <dt class="os-dt">Account</dt>
                            <dd class="os-dd">
                                <Link v-if="order.user" :href="route('admin.users.show', order.user_id!)"
                                    class="os-link">
                                {{ order.user.name }} (#{{ order.user_id }})
                                </Link>
                                <span v-else style="color:var(--bb-muted); font-style:italic">Guest</span>
                            </dd>
                        </div>
                    </div>
                </section>

                <!-- Addresses -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Shipping &amp; Billing</h2>
                    <div class="os-address-grid">
                        <div>
                            <p class="os-address-label">Shipping Address</p>
                            <address class="os-address">
                                <span>{{ order.shipping_line_1 }}</span>
                                <span v-if="order.shipping_line_2">{{ order.shipping_line_2 }}</span>
                                <span>{{ order.shipping_city }}<template v-if="order.shipping_county">, {{
                                    order.shipping_county }}</template></span>
                                <span>{{ order.shipping_postcode }}</span>
                            </address>
                        </div>
                        <div>
                            <p class="os-address-label">Billing Address</p>
                            <address class="os-address">
                                <span>{{ order.billing_line_1 }}</span>
                                <span v-if="order.billing_line_2">{{ order.billing_line_2 }}</span>
                                <span>{{ order.billing_city }}<template v-if="order.billing_county">, {{
                                    order.billing_county }}</template></span>
                                <span>{{ order.billing_postcode }}</span>
                            </address>
                        </div>
                    </div>
                </section>

                <!-- Payment -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Payment</h2>
                    <div class="os-dl">
                        <div class="os-dl-item">
                            <dt class="os-dt">Method</dt>
                            <dd class="os-dd" style="text-transform:capitalize">{{ order.payment_type }}</dd>
                        </div>
                        <div class="os-dl-item os-dl-item--full">
                            <dt class="os-dt">Stripe Payment Intent</dt>
                            <dd class="os-dd adm-td--mono" style="font-size:0.8rem; word-break:break-all">
                                {{ order.payment_intent_id }}
                            </dd>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ── Right / Sidebar ── -->
            <div class="os-sidebar">

                <!-- Totals — sticky on desktop -->
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Order Total</h2>
                    <div class="os-totals">
                        <div class="os-total-row">
                            <span class="os-total-lbl">Subtotal</span>
                            <span>{{ fmtCurrency(order.cost_total) }}</span>
                        </div>
                        <div class="os-total-row">
                            <span class="os-total-lbl">Shipping</span>
                            <span>{{ Number(order.shipping_total) === 0 ? 'FREE' : fmtCurrency(order.shipping_total)
                                }}</span>
                        </div>
                        <div v-if="vatRegistered" class="os-total-row">
                            <span class="os-total-lbl">VAT (20% inc.)</span>
                            <span>{{ fmtCurrency(order.tax_total) }}</span>
                        </div>
                        <div v-if="Number(order.voucher_discount) > 0" class="os-total-row os-total-row--discount">
                            <span>Discount</span>
                            <span>−{{ fmtCurrency(order.voucher_discount) }}</span>
                        </div>
                        <div class="os-total-row os-total-grand">
                            <span>Grand Total</span>
                            <span class="os-grand-val">{{ fmtCurrency(order.grand_total) }}</span>
                        </div>
                    </div>
                </section>

                <!-- Status update -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Update Status</h2>
                    <div class="adm-field">
                        <label class="adm-label" for="status-select">New Status</label>
                        <select id="status-select" v-model="selectedStatus" class="adm-select">
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <button @click="updateStatus" :disabled="updatingStatus || selectedStatus === order.status"
                        class="adm-submit">
                        <svg v-if="updatingStatus" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ updatingStatus ? 'Updating…' : 'Save Status' }}
                    </button>
                </section>

                <!-- Email actions -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Email Customer</h2>
                    <div class="adm-field">
                        <label class="adm-label" for="tracking-url">
                            Tracking URL
                            <span class="adm-label-note">(optional)</span>
                        </label>
                        <input id="tracking-url" v-model="trackingUrl" type="url"
                            placeholder="https://track.royalmail.com/…" class="adm-input" />
                        <p class="os-hint">Used in both dispatch email and status update.</p>
                    </div>
                    <button @click="sendDispatch" :disabled="sendingDispatch"
                        class="adm-btn adm-btn--primary adm-btn--full">
                        <svg v-if="sendingDispatch" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2 11 13" />
                            <path d="M22 2 15 22 11 13 2 9l20-7z" />
                        </svg>
                        {{ sendingDispatch ? 'Sending…' : 'Send Dispatch Email' }}
                    </button>
                    <button @click="resendConfirmation" :disabled="sendingConfirmation"
                        class="adm-btn adm-btn--ghost adm-btn--full">
                        <svg v-if="sendingConfirmation" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(26,26,46,0.15)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#9b84d4" stroke-width="3"
                                stroke-linecap="round" />
                        </svg>
                        {{ sendingConfirmation ? 'Sending…' : 'Resend Confirmation' }}
                    </button>
                </section>

                <!-- Quick links -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Quick Links</h2>
                    <div class="os-links">
                        <a :href="route('admin.batch-sheets.create', { order_id: order.id })" class="os-link-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="12" y1="18" x2="12" y2="12" />
                                <line x1="9" y1="15" x2="15" y2="15" />
                            </svg>
                            Create Batch Sheet
                        </a>
                        <a v-if="order.user_id" :href="route('admin.users.show', order.user_id)" class="os-link-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            View Customer Account
                        </a>
                        <a :href="route('admin.orders.index')" class="os-link-item os-link-item--muted">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                            All Orders
                        </a>
                    </div>
                </section>

            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/*
 * Page-specific styles only — prefix: os- (order show)
 * All shared styles come from admin-design-system.css
 */

/* ── Two-column layout ── */
.os-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .os-layout {
        grid-template-columns: 1fr;
    }
}

.os-main {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.os-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ── Flush card section title ── */
.os-section-title {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--bb-muted);
    padding: 1rem 1.5rem 0.85rem;
    border-bottom: 1px solid var(--bb-border);
}

/* ── Item rows ── */
.os-item-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1.5rem;
    border-bottom: 1px solid var(--bb-border);
}

.os-item-row:last-child {
    border-bottom: none;
}

.os-item-info {
    flex: 1;
    min-width: 0;
}

.os-item-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--bb-text);
}

.os-item-meta {
    font-size: 0.75rem;
    color: var(--bb-muted);
    margin-top: 0.15rem;
}

.os-sep {
    margin: 0 0.25rem;
    opacity: 0.5;
}

.os-item-right {
    text-align: right;
    flex-shrink: 0;
}

.os-item-price {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--bb-text);
}

.os-item-qty {
    font-size: 0.75rem;
    color: var(--bb-muted);
    margin-top: 0.1rem;
}

/* ── Definition list ── */
.os-dl {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.5rem;
}

@media (max-width: 640px) {
    .os-dl {
        grid-template-columns: 1fr;
    }
}

.os-dl-item--full {
    grid-column: 1 / -1;
}

.os-dt {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bb-muted);
    margin-bottom: 0.2rem;
}

.os-dd {
    font-size: 0.88rem;
    font-weight: 500;
    color: var(--bb-text);
}

.os-link {
    color: var(--bb-lav-d);
    text-decoration: none;
    transition: color 0.15s;
}

.os-link:hover {
    color: var(--bb-navy);
    text-decoration: underline;
}

/* ── Addresses ── */
.os-address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 640px) {
    .os-address-grid {
        grid-template-columns: 1fr;
    }
}

.os-address-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--bb-muted);
    margin-bottom: 0.5rem;
}

.os-address {
    font-style: normal;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    font-size: 0.88rem;
    color: var(--bb-text);
    line-height: 1.55;
}

/* ── Totals ── */
.os-totals {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.os-total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
}

.os-total-lbl {
    color: var(--bb-muted);
}

.os-total-row--discount {
    color: var(--bb-sage-d);
    font-weight: 600;
}

.os-total-grand {
    margin-top: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--bb-border);
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--bb-text);
}

.os-grand-val {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--bb-lav-d);
}

/* ── Hint text ── */
.os-hint {
    font-size: 0.72rem;
    color: var(--bb-muted);
}

/* ── Quick links ── */
.os-links {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.os-link-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem 0.85rem;
    border-radius: var(--bb-radius);
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--bb-text);
    text-decoration: none;
    transition: background 0.12s, border-color 0.12s;
}

.os-link-item:hover {
    background: #faf8ff;
    border-color: var(--bb-lav);
}

.os-link-item--muted {
    color: var(--bb-muted);
}

.os-link-item--muted:hover {
    color: var(--bb-text);
}

/* ══════════════════════════════════════════════════════════
   ORDER CHECKLIST  — prefix: cl-
   ══════════════════════════════════════════════════════════ */

/* Header row */
.cl-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem 0.75rem;
    border-bottom: 1px solid var(--bb-border);
}

.cl-header-left {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.cl-title {
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--bb-muted);
}

.cl-progress-pill {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.55rem;
    border-radius: var(--bb-radius-pill);
    background: #f0edf8;
    color: var(--bb-lav-d);
    border: 1px solid var(--bb-lav);
    transition: background 0.2s, color 0.2s;
}

.cl-progress-pill--done {
    background: var(--bb-green-bg);
    color: var(--bb-sage-d);
    border-color: var(--bb-green-border);
}

.cl-reset-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-family: var(--bb-font);
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--bb-muted);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: var(--bb-radius-sm);
    transition: background 0.12s, color 0.12s;
}

.cl-reset-btn:hover {
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

/* Progress bar */
.cl-bar-track {
    height: 4px;
    background: var(--bb-border);
    overflow: hidden;
}

.cl-bar-fill {
    height: 100%;
    background: var(--bb-lav-d);
    transition: width 0.35s ease, background 0.35s ease;
    min-width: 0;
}

.cl-bar-fill--done {
    background: var(--bb-sage-d);
}

/* All done banner */
.cl-done-banner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.5rem;
    background: var(--bb-green-bg);
    color: var(--bb-sage-d);
    font-size: 0.82rem;
    font-weight: 700;
    border-bottom: 1px solid var(--bb-green-border);
}

/* Group */
.cl-group {
    border-bottom: 1px solid var(--bb-border);
}

.cl-group:last-child {
    border-bottom: none;
}

.cl-group-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-family: var(--bb-font);
    text-align: left;
    transition: background 0.12s;
}

.cl-group-header:hover {
    background: var(--bb-cream);
}

/* Subtle left border accent per colour */
.cl-group-header--lav:hover {
    background: #f8f6ff;
}

.cl-group-header--blush:hover {
    background: #fff8f9;
}

.cl-group-header--sage:hover {
    background: #f4faf4;
}

.cl-group-header--peach:hover {
    background: #fffbf5;
}

.cl-group-left {
    display: flex;
    align-items: center;
    gap: 0.55rem;
}

.cl-group-right {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-shrink: 0;
}

.cl-group-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.cl-group-dot--lav {
    background: var(--bb-lav-d);
}

.cl-group-dot--blush {
    background: var(--bb-blush-d);
}

.cl-group-dot--sage {
    background: var(--bb-sage-d);
}

.cl-group-dot--peach {
    background: var(--bb-peach-d);
}

.cl-group-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--bb-text);
}

.cl-group-count {
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--bb-muted);
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
    padding: 0.1rem 0.4rem;
    border-radius: var(--bb-radius-pill);
}

.cl-group-count--done {
    background: var(--bb-green-bg);
    color: var(--bb-sage-d);
    border-color: var(--bb-green-border);
}

.cl-group-check-all {
    font-family: var(--bb-font);
    font-size: 0.68rem;
    font-weight: 600;
    color: var(--bb-muted);
    background: none;
    border: 1px solid var(--bb-border);
    border-radius: var(--bb-radius-pill);
    padding: 0.15rem 0.55rem;
    cursor: pointer;
    transition: background 0.12s, color 0.12s, border-color 0.12s;
    white-space: nowrap;
}

.cl-group-check-all:hover {
    background: var(--bb-cream);
    color: var(--bb-text);
}

.cl-group-check-all--active {
    background: var(--bb-green-bg);
    color: var(--bb-sage-d);
    border-color: var(--bb-green-border);
}

.cl-chevron {
    color: var(--bb-muted);
    transition: transform 0.2s;
    flex-shrink: 0;
}

.cl-chevron--open {
    transform: rotate(180deg);
}

/* Items list */
.cl-items {
    padding: 0.25rem 1.5rem 0.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.cl-item {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.45rem 0.6rem;
    border-radius: var(--bb-radius-sm);
    cursor: pointer;
    transition: background 0.1s;
    user-select: none;
}

.cl-item:hover {
    background: var(--bb-cream);
}

.cl-item--checked .cl-item-label {
    color: var(--bb-muted);
    text-decoration: line-through;
}

/* Custom checkbox */
.cl-checkbox-wrap {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    border-radius: var(--bb-radius-sm);
    border: 1.5px solid var(--bb-border);
    background: var(--bb-surface);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1px;
    transition: background 0.15s, border-color 0.15s;
    cursor: pointer;
}

.cl-item--checked .cl-checkbox-wrap {
    background: var(--bb-lav-d);
    border-color: var(--bb-lav-d);
    color: #fff;
}

.cl-item-label {
    font-size: 0.85rem;
    color: var(--bb-text);
    line-height: 1.4;
    flex: 1;
}

.os-item-row--gv {
    background: rgba(201, 168, 76, 0.04);
    border-left: 3px solid #c9a84c;
}
</style>
