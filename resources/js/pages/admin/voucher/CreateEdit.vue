<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import axios from 'axios';

interface Product { id: number; name: string; mpn: string; }

interface Voucher {
    id: number;
    code: string;
    description: string | null;
    type: 'percentage' | 'fixed';
    value: string;
    minimum_order_value: string | null;
    applies_to_all_products: boolean;
    stackable: boolean;
    new_customers_only: boolean;
    single_use_per_user: boolean;
    max_uses: number | null;
    valid_from: string | null;
    valid_until: string | null;
    is_active: boolean;
}

const props = defineProps<{
    voucher?: Voucher;
    selectedProductIds?: number[];
    products: Product[];
    isEditing: boolean;
}>();

const form = useForm({
    code: props.voucher?.code ?? '',
    description: props.voucher?.description ?? '',
    type: props.voucher?.type ?? 'percentage',
    value: props.voucher?.value ?? '',
    minimum_order_value: props.voucher?.minimum_order_value ?? '',
    applies_to_all_products: props.voucher?.applies_to_all_products ?? true,
    product_ids: (props.selectedProductIds ?? []) as number[],
    stackable: props.voucher?.stackable ?? false,
    new_customers_only: props.voucher?.new_customers_only ?? false,
    single_use_per_user: props.voucher?.single_use_per_user ?? false,
    max_uses: props.voucher?.max_uses?.toString() ?? '',
    valid_from: props.voucher?.valid_from?.substring(0, 16) ?? '',
    valid_until: props.voucher?.valid_until?.substring(0, 16) ?? '',
    is_active: props.voucher?.is_active ?? true,
});

const title = computed(() => props.isEditing ? `Edit Voucher: ${props.voucher?.code}` : 'Create Voucher');
const generatingCode = ref(false);

async function generateCode() {
    generatingCode.value = true;
    try {
        const { data } = await axios.get(route('admin.vouchers.generate-code'));
        form.code = data.code;
    } finally {
        generatingCode.value = false;
    }
}

function toggleProduct(id: number, checked: boolean) {
    if (checked) {
        if (!form.product_ids.includes(id)) form.product_ids.push(id);
    } else {
        form.product_ids = form.product_ids.filter(p => p !== id);
    }
}

function submit() {
    if (props.isEditing && props.voucher) {
        form.put(route('admin.vouchers.update', { voucher: props.voucher.id }), { preserveScroll: true });
    } else {
        form.post(route('admin.vouchers.store'), { preserveScroll: true });
    }
}

const valuePlaceholder = computed(() =>
    form.type === 'percentage' ? 'e.g. 10 (for 10%)' : 'e.g. 5.00 (for £5 off)'
);
</script>

<template>
    <AdminLayout>

        <Head :title="title" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">{{ title }}</h2>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- ── Left column ─────────────────────────────────────────────── -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Core details -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Voucher Details
                        </h3>

                        <!-- Code -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-copy mb-1">Voucher Code</label>
                            <div class="flex gap-2">
                                <input type="text" v-model="form.code" required placeholder="e.g. SUMMER20"
                                    class="flex-1 rounded-lg border-2 border-copy bg-foreground p-3 text-copy font-mono uppercase focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.code }"
                                    @input="form.code = (form.code as string).toUpperCase()" />
                                <button type="button" @click="generateCode" :disabled="generatingCode"
                                    class="rounded-lg border-2 border-copy px-4 text-sm font-medium text-copy-light hover:text-copy hover:bg-secondary-light transition disabled:opacity-50">
                                    {{ generatingCode ? '...' : 'Generate' }}
                                </button>
                            </div>
                            <p class="text-xs text-copy-light mt-1">Only uppercase letters, numbers, hyphens and
                                underscores.</p>
                            <div v-if="form.errors.code" class="text-xs text-error mt-1">{{ form.errors.code }}</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-copy mb-1">Description (internal note)</label>
                            <input type="text" v-model="form.description" placeholder="e.g. Summer sale 2025 campaign"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm" />
                        </div>

                        <!-- Type + Value -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-copy mb-1">Discount Type</label>
                                <select v-model="form.type"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount (£)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-copy mb-1">
                                    Value <span class="text-copy-light">({{ form.type === 'percentage' ? '%' : '£'
                                    }})</span>
                                </label>
                                <input type="number" v-model="form.value" required min="0.01"
                                    :max="form.type === 'percentage' ? 100 : undefined" step="0.01"
                                    :placeholder="valuePlaceholder"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.value }" />
                                <div v-if="form.errors.value" class="text-xs text-error mt-1">{{ form.errors.value }}
                                </div>
                            </div>
                        </div>

                        <!-- Minimum order value -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-copy mb-1">Minimum Order Value (£) <span
                                    class="text-copy-light">— optional</span></label>
                            <input type="number" v-model="form.minimum_order_value" min="0" step="0.01"
                                placeholder="Leave blank for no minimum"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm" />
                        </div>
                    </div>
                </div>

                <!-- Validity -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Validity & Limits
                        </h3>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-copy mb-1">Valid From <span
                                        class="text-copy-light">— optional</span></label>
                                <input type="datetime-local" v-model="form.valid_from"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-copy mb-1">Valid Until <span
                                        class="text-copy-light">— optional</span></label>
                                <input type="datetime-local" v-model="form.valid_until"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.valid_until }" />
                                <div v-if="form.errors.valid_until" class="text-xs text-error mt-1">{{
                                    form.errors.valid_until }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-copy mb-1">Maximum Uses <span
                                    class="text-copy-light">— optional, leave blank for unlimited</span></label>
                            <input type="number" v-model="form.max_uses" min="1" step="1" placeholder="Unlimited"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary shadow-sm" />
                        </div>
                    </div>
                </div>

                <!-- Product restrictions -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-2 border-b-2 border-copy-light pb-2">Product
                            Restrictions</h3>
                        <p class="text-xs text-copy-light mb-4">By default the voucher applies to all products. Enable
                            restrictions to limit it to specific ones.</p>

                        <label class="flex items-center gap-3 mb-4 cursor-pointer">
                            <input type="checkbox" :checked="!form.applies_to_all_products"
                                @change="form.applies_to_all_products = !($event.target as HTMLInputElement).checked"
                                class="size-5 border-2 border-copy text-primary" />
                            <span class="text-sm font-medium text-copy">Restrict to specific products</span>
                        </label>

                        <div v-if="!form.applies_to_all_products"
                            class="max-h-64 overflow-y-auto pr-2 border-t border-copy-light pt-3">
                            <p v-if="products.length === 0" class="text-sm text-copy-light italic">No enabled products
                                found.</p>
                            <ul class="space-y-2">
                                <li v-for="product in products" :key="product.id">
                                    <label :for="`product-${product.id}`"
                                        class="flex items-center gap-3 cursor-pointer hover:text-primary transition">
                                        <input type="checkbox" :id="`product-${product.id}`"
                                            :checked="form.product_ids.includes(product.id)"
                                            @change="toggleProduct(product.id, ($event.target as HTMLInputElement).checked)"
                                            class="size-4 border-2 border-copy text-primary" />
                                        <span class="text-sm text-copy">{{ product.name }}</span>
                                        <span class="text-xs text-copy-light font-mono">{{ product.mpn }}</span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Right column ────────────────────────────────────────────── -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Status & Submit -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Status</h3>

                        <label class="flex items-center gap-3 cursor-pointer mb-6">
                            <input type="checkbox" v-model="form.is_active"
                                class="size-5 border-2 border-copy text-primary" />
                            <span class="text-sm font-medium text-copy">Voucher is active</span>
                        </label>

                        <button type="submit" :disabled="form.processing"
                            class="w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors rounded-lg disabled:opacity-50"
                            style="background-color: var(--primary); color: var(--primary-content);">
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Update Voucher' : 'Create Voucher') }}
                        </button>

                        <div v-if="form.isDirty" class="mt-2 text-center text-sm text-yellow-600 font-medium">Unsaved
                            changes</div>
                    </div>
                </div>

                <!-- Behaviour flags -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Behaviour</h3>

                        <ul class="space-y-4">
                            <li>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" v-model="form.stackable"
                                        class="mt-0.5 size-5 border-2 border-copy text-primary" />
                                    <div>
                                        <span class="block text-sm font-medium text-copy">Stackable</span>
                                        <span class="text-xs text-copy-light">Can be used alongside other active
                                            discounts</span>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" v-model="form.new_customers_only"
                                        class="mt-0.5 size-5 border-2 border-copy text-primary" />
                                    <div>
                                        <span class="block text-sm font-medium text-copy">New customers only</span>
                                        <span class="text-xs text-copy-light">Only usable by users with no previous
                                            successful orders</span>
                                    </div>
                                </label>
                            </li>
                            <li>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" v-model="form.single_use_per_user"
                                        class="mt-0.5 size-5 border-2 border-copy text-primary" />
                                    <div>
                                        <span class="block text-sm font-medium text-copy">One use per customer</span>
                                        <span class="text-xs text-copy-light">Each registered user can only use this
                                            voucher once</span>
                                    </div>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
