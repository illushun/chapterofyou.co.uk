<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as any) ?? {});
const selectedStatus = ref(props.order.status);
const updatingStatus = ref(false);
const sendingDispatch = ref(false);
const sendingConfirmation = ref(false);
const trackingUrl = ref('');

const fmt = (v: number | string | null | undefined) =>
    `£${(Number(v) || 0).toFixed(2)}`;

const fmtDate = (d: string) =>
    new Date(d).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });

const statusColour: Record<string, string> = {
    successful: 'bg-green-100 text-green-800 border-green-300',
    shipped: 'bg-emerald-100 text-emerald-800 border-emerald-300',
    processing: 'bg-blue-100 text-blue-800 border-blue-300',
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-300',
    cancelled: 'bg-red-100 text-red-800 border-red-300',
    failed: 'bg-red-100 text-red-800 border-red-300',
};
const statusClass = (s: string) => statusColour[s] ?? 'bg-gray-100 text-gray-700 border-gray-300';

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
</script>

<template>
    <AdminLayout>

        <Head :title="`Order #${order.id}`" />

        <!-- Flash message -->
        <div v-if="flash.success"
            class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            ✓ {{ flash.success }}
        </div>

        <!-- Page header -->
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3 border-b-2 border-copy pb-3">
            <div>
                <h2 class="text-3xl font-black">Order #COY-{{ String(order.id).padStart(6, '0') }}</h2>
                <p class="text-copy-light text-sm mt-0.5">Placed {{ fmtDate(order.created_at) }}</p>
            </div>
            <span :class="['px-3 py-1 rounded-full text-sm font-bold uppercase border', statusClass(order.status)]">
                {{ order.status }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- ── Left / Main ── -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Items -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">
                            Items ({{ order.items.length }})
                        </h3>
                        <div v-for="item in order.items" :key="item.id"
                            class="flex items-center justify-between py-3 border-b border-copy-light/40 last:border-b-0 gap-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-copy truncate">{{ item.product.name }}</p>
                                <p class="text-xs text-copy-light mt-0.5">
                                    MPN: {{ item.product.mpn }} · Unit: {{ fmt(item.product_cost) }}
                                </p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-copy">{{ fmt(item.product_total) }}</p>
                                <p class="text-xs text-copy-light">Qty: {{ item.quantity }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer details -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">Customer</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-copy-light font-medium">Name</dt>
                                <dd class="text-copy font-semibold">{{ order.first_name }} {{ order.last_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-copy-light font-medium">Email</dt>
                                <dd>
                                    <a :href="`mailto:${order.email}`" class="text-primary hover:underline break-all">{{
                                        order.email }}</a>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-copy-light font-medium">Phone</dt>
                                <dd class="text-copy">{{ order.telephone || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-copy-light font-medium">Account</dt>
                                <dd>
                                    <Link v-if="order.user" :href="route('admin.users.show', order.user_id!)"
                                        class="text-primary hover:underline">
                                    {{ order.user.name }} (#{{ order.user_id }})
                                    </Link>
                                    <span v-else class="text-copy-light italic">Guest</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">
                            Shipping &amp; Billing
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-copy">
                            <div>
                                <p class="font-bold mb-1">Shipping Address</p>
                                <address class="not-italic leading-relaxed text-copy-light">
                                    <p>{{ order.shipping_line_1 }}</p>
                                    <p v-if="order.shipping_line_2">{{ order.shipping_line_2 }}</p>
                                    <p>{{ order.shipping_city }}<span v-if="order.shipping_county">, {{
                                        order.shipping_county }}</span></p>
                                    <p>{{ order.shipping_postcode }}</p>
                                </address>
                            </div>
                            <div>
                                <p class="font-bold mb-1">Billing Address</p>
                                <address class="not-italic leading-relaxed text-copy-light">
                                    <p>{{ order.billing_line_1 }}</p>
                                    <p v-if="order.billing_line_2">{{ order.billing_line_2 }}</p>
                                    <p>{{ order.billing_city }}<span v-if="order.billing_county">, {{
                                        order.billing_county }}</span></p>
                                    <p>{{ order.billing_postcode }}</p>
                                </address>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment info -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">Payment</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="text-copy-light font-medium">Method</dt>
                                <dd class="text-copy font-semibold capitalize">{{ order.payment_type }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-copy-light font-medium">Stripe Payment Intent</dt>
                                <dd class="text-copy font-mono text-xs break-all">{{ order.payment_intent_id }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <!-- ── Right / Sidebar ── -->
            <div class="space-y-5">

                <!-- Financial summary -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-4 border-b border-copy-light pb-2">Order Total</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-copy-light">
                                <span>Subtotal</span>
                                <span>{{ fmt(order.cost_total) }}</span>
                            </div>
                            <div class="flex justify-between text-copy-light">
                                <span>Shipping</span>
                                <span>{{ Number(order.shipping_total) === 0 ? 'FREE' : fmt(order.shipping_total)
                                }}</span>
                            </div>
                            <div class="flex justify-between text-copy-light">
                                <span>VAT (20%)</span>
                                <span>{{ fmt(order.tax_total) }}</span>
                            </div>
                            <div v-if="Number(order.voucher_discount) > 0"
                                class="flex justify-between text-green-700 font-medium">
                                <span>Discount</span>
                                <span>-{{ fmt(order.voucher_discount) }}</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t-2 border-copy-light flex justify-between items-center">
                            <span class="font-black text-copy text-lg">Grand Total</span>
                            <span class="font-black text-primary text-2xl">{{ fmt(order.grand_total) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status update -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-3 border-b border-copy-light pb-2">Update Status</h3>
                        <select v-model="selectedStatus"
                            class="w-full rounded-lg border-2 border-copy bg-foreground px-3 py-2 text-sm text-copy mb-3 focus:outline-none">
                            <option v-for="(label, value) in statuses" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <button @click="updateStatus" :disabled="updatingStatus || selectedStatus === order.status"
                            class="w-full rounded-lg border-2 border-copy py-2 text-sm font-bold transition disabled:opacity-40"
                            style="background-color: var(--primary); color: var(--primary-content);">
                            {{ updatingStatus ? 'Updating…' : 'Save Status' }}
                        </button>
                    </div>
                </div>

                <!-- Email actions -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-3 border-b border-copy-light pb-2">Email Customer</h3>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs text-copy-light font-medium mb-1">
                                    Tracking URL <span class="text-copy-light">(optional)</span>
                                </label>
                                <input v-model="trackingUrl" type="url" placeholder="https://track.royalmail.com/..."
                                    class="w-full rounded-lg border border-copy-light bg-foreground px-3 py-1.5 text-xs text-copy focus:outline-none focus:border-copy mb-1" />
                                <p class="text-xs text-copy-light">Used in both dispatch email and status update.</p>
                            </div>
                            <button @click="sendDispatch" :disabled="sendingDispatch"
                                class="w-full rounded-lg border-2 border-copy py-2 text-sm font-bold transition hover:bg-secondary-light disabled:opacity-40">
                                {{ sendingDispatch ? 'Sending…' : 'Send Dispatch Email' }}
                            </button>
                            <button @click="resendConfirmation" :disabled="sendingConfirmation"
                                class="w-full rounded-lg border-2 border-copy py-2 text-sm font-medium text-copy-light hover:text-copy hover:bg-secondary-light transition disabled:opacity-40">
                                {{ sendingConfirmation ? 'Sending…' : 'Resend Confirmation' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick links -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-5">
                        <h3 class="text-lg font-bold text-copy mb-3 border-b border-copy-light pb-2">Quick Links</h3>
                        <div class="space-y-2 text-sm">
                            <a :href="route('admin.batch-sheets.create', { order_id: order.id })"
                                class="flex items-center gap-2 rounded-lg border border-copy-light px-3 py-2 text-copy hover:bg-secondary-light transition">
                                Create Batch Sheet
                            </a>
                            <a v-if="order.user_id" :href="route('admin.users.show', order.user_id)"
                                class="flex items-center gap-2 rounded-lg border border-copy-light px-3 py-2 text-copy hover:bg-secondary-light transition">
                                View Customer Account
                            </a>
                            <a :href="route('admin.orders.index')"
                                class="flex items-center gap-2 rounded-lg border border-copy-light px-3 py-2 text-copy-light hover:text-copy hover:bg-secondary-light transition">
                                All Orders
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
