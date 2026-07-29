<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

interface Product {
    id: number;
    name: string;
    mpn: string;
}

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

const title = computed(() =>
    props.isEditing ? `Edit Voucher: ${props.voucher?.code}` : 'New Voucher',
);
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
        form.product_ids = form.product_ids.filter((p) => p !== id);
    }
}

function submit() {
    if (props.isEditing && props.voucher) {
        form.put(
            route('admin.vouchers.update', { voucher: props.voucher.id }),
            { preserveScroll: true },
        );
    } else {
        form.post(route('admin.vouchers.store'), { preserveScroll: true });
    }
}

const valuePlaceholder = computed(() =>
    form.type === 'percentage' ? 'e.g. 10 (for 10%)' : 'e.g. 5.00 (for £5 off)',
);
</script>

<template>
    <AdminLayout>
        <Head :title="`${title} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.vouchers.index')"
                        class="adm-breadcrumb a"
                        >Vouchers</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="adm-title">{{ title }}</h1>
            </div>
            <div v-if="form.isDirty" class="adm-unsaved">Unsaved changes</div>
        </div>

        <form @submit.prevent="submit" class="adm-form-grid">
            <!-- Left column -->
            <div class="adm-form-left">
                <!-- Core details -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Voucher Details</h2>

                    <div class="adm-field">
                        <label class="adm-label">Voucher Code</label>
                        <div style="display: flex; gap: 0.5rem">
                            <input
                                type="text"
                                v-model="form.code"
                                required
                                placeholder="e.g. SUMMER20"
                                class="adm-input"
                                style="text-transform: uppercase; flex: 1"
                                :class="{ 'adm-input--err': form.errors.code }"
                                @input="
                                    form.code = (
                                        form.code as string
                                    ).toUpperCase()
                                "
                            />
                            <button
                                type="button"
                                @click="generateCode"
                                :disabled="generatingCode"
                                class="adm-btn adm-btn--ghost"
                            >
                                {{ generatingCode ? '...' : 'Generate' }}
                            </button>
                        </div>
                        <p class="adm-field-note">
                            Only uppercase letters, numbers, hyphens and
                            underscores.
                        </p>
                        <p v-if="form.errors.code" class="adm-err">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label"
                            >Description
                            <span class="adm-label-note"
                                >(internal note)</span
                            ></label
                        >
                        <input
                            type="text"
                            v-model="form.description"
                            placeholder="e.g. Summer sale 2025 campaign"
                            class="adm-input"
                        />
                    </div>

                    <div class="adm-field-row">
                        <div class="adm-field">
                            <label class="adm-label">Discount Type</label>
                            <select v-model="form.type" class="adm-select">
                                <option value="percentage">
                                    Percentage (%)
                                </option>
                                <option value="fixed">Fixed Amount (£)</option>
                            </select>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label"
                                >Value
                                <span class="adm-label-note"
                                    >({{
                                        form.type === 'percentage' ? '%' : '£'
                                    }})</span
                                ></label
                            >
                            <input
                                type="number"
                                v-model="form.value"
                                required
                                min="0.01"
                                :max="
                                    form.type === 'percentage' ? 100 : undefined
                                "
                                step="0.01"
                                :placeholder="valuePlaceholder"
                                class="adm-input"
                                :class="{ 'adm-input--err': form.errors.value }"
                            />
                            <p v-if="form.errors.value" class="adm-err">
                                {{ form.errors.value }}
                            </p>
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label"
                            >Minimum Order Value (£)
                            <span class="adm-label-note">optional</span></label
                        >
                        <div class="adm-prefix-wrap">
                            <span class="adm-prefix">£</span>
                            <input
                                type="number"
                                v-model="form.minimum_order_value"
                                min="0"
                                step="0.01"
                                placeholder="Leave blank for no minimum"
                                class="adm-input adm-input--prefixed"
                            />
                        </div>
                    </div>
                </section>

                <!-- Validity -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Validity &amp; Limits</h2>

                    <div class="adm-field-row">
                        <div class="adm-field">
                            <label class="adm-label"
                                >Valid From
                                <span class="adm-label-note"
                                    >optional</span
                                ></label
                            >
                            <input
                                type="datetime-local"
                                v-model="form.valid_from"
                                class="adm-input"
                            />
                        </div>
                        <div class="adm-field">
                            <label class="adm-label"
                                >Valid Until
                                <span class="adm-label-note"
                                    >optional</span
                                ></label
                            >
                            <input
                                type="datetime-local"
                                v-model="form.valid_until"
                                class="adm-input"
                                :class="{
                                    'adm-input--err': form.errors.valid_until,
                                }"
                            />
                            <p v-if="form.errors.valid_until" class="adm-err">
                                {{ form.errors.valid_until }}
                            </p>
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label"
                            >Maximum Uses
                            <span class="adm-label-note"
                                >optional, leave blank for unlimited</span
                            ></label
                        >
                        <input
                            type="number"
                            v-model="form.max_uses"
                            min="1"
                            step="1"
                            placeholder="Unlimited"
                            class="adm-input"
                        />
                    </div>
                </section>

                <!-- Product restrictions -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Product Restrictions</h2>
                    <p class="adm-field-note" style="margin-bottom: 0.85rem">
                        By default the voucher applies to all products. Enable
                        restrictions to limit it to specific ones.
                    </p>

                    <label
                        class="adm-check-item"
                        style="margin-bottom: 0.75rem"
                    >
                        <input
                            type="checkbox"
                            :checked="!form.applies_to_all_products"
                            @change="
                                form.applies_to_all_products = !(
                                    $event.target as HTMLInputElement
                                ).checked
                            "
                            class="adm-checkbox"
                        />
                        Restrict to specific products
                    </label>

                    <div
                        v-if="!form.applies_to_all_products"
                        class="adm-check-list"
                        style="
                            max-height: 16rem;
                            border-top: 1px dashed var(--adm-line);
                            padding-top: 0.75rem;
                        "
                    >
                        <p v-if="products.length === 0" class="adm-empty-sub">
                            No enabled products found.
                        </p>
                        <label
                            v-for="product in products"
                            :key="product.id"
                            class="adm-check-item"
                            :class="{
                                'adm-check-item--active':
                                    form.product_ids.includes(product.id),
                            }"
                        >
                            <input
                                type="checkbox"
                                :checked="form.product_ids.includes(product.id)"
                                @change="
                                    toggleProduct(
                                        product.id,
                                        ($event.target as HTMLInputElement)
                                            .checked,
                                    )
                                "
                                class="adm-checkbox"
                            />
                            <span style="flex: 1">{{ product.name }}</span>
                            <span class="adm-td--mono">{{ product.mpn }}</span>
                        </label>
                    </div>
                </section>
            </div>

            <!-- Right column -->
            <div class="adm-form-right">
                <!-- Status & Submit -->
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Status</h2>

                    <label
                        class="adm-check-item"
                        style="margin-bottom: 1.25rem"
                    >
                        <input
                            type="checkbox"
                            v-model="form.is_active"
                            class="adm-checkbox"
                        />
                        Voucher is active
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="adm-submit"
                    >
                        {{
                            form.processing
                                ? 'Saving...'
                                : isEditing
                                  ? 'Update Voucher'
                                  : 'Create Voucher'
                        }}
                    </button>
                    <p v-if="form.isDirty" class="adm-unsaved-inline">
                        Unsaved changes
                    </p>
                </section>

                <!-- Behaviour flags -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Behaviour</h2>

                    <div
                        style="
                            display: flex;
                            flex-direction: column;
                            gap: 0.75rem;
                        "
                    >
                        <label
                            class="adm-check-item"
                            style="align-items: flex-start"
                        >
                            <input
                                type="checkbox"
                                v-model="form.stackable"
                                class="adm-checkbox"
                                style="margin-top: 0.15rem"
                            />
                            <span>
                                <span
                                    style="
                                        display: block;
                                        color: var(--adm-ink);
                                        font-weight: 500;
                                    "
                                    >Stackable</span
                                >
                                <span class="adm-field-note"
                                    >Can be used alongside other active
                                    discounts</span
                                >
                            </span>
                        </label>
                        <label
                            class="adm-check-item"
                            style="align-items: flex-start"
                        >
                            <input
                                type="checkbox"
                                v-model="form.new_customers_only"
                                class="adm-checkbox"
                                style="margin-top: 0.15rem"
                            />
                            <span>
                                <span
                                    style="
                                        display: block;
                                        color: var(--adm-ink);
                                        font-weight: 500;
                                    "
                                    >New customers only</span
                                >
                                <span class="adm-field-note"
                                    >Only usable by users with no previous
                                    successful orders</span
                                >
                            </span>
                        </label>
                        <label
                            class="adm-check-item"
                            style="align-items: flex-start"
                        >
                            <input
                                type="checkbox"
                                v-model="form.single_use_per_user"
                                class="adm-checkbox"
                                style="margin-top: 0.15rem"
                            />
                            <span>
                                <span
                                    style="
                                        display: block;
                                        color: var(--adm-ink);
                                        font-weight: 500;
                                    "
                                    >One use per customer</span
                                >
                                <span class="adm-field-note"
                                    >Each registered user can only use this
                                    voucher once</span
                                >
                            </span>
                        </label>
                    </div>
                </section>
            </div>
        </form>
    </AdminLayout>
</template>
