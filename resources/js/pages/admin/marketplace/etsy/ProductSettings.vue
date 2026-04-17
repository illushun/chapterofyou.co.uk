<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useAdmin } from '@/composables/useAdmin';

interface ProductData {
    id: number;
    mpn: string;
    name: string;
    description: string;
    cost: number;
    stock_qty: number;
    images: { image: string }[];
}

interface SettingData {
    enabled: boolean;
    override_title: string;
    override_description: string;
    override_price: number | null;
    override_tags: string;
}

interface ListingData {
    listing_id: string;
    status: string;
    last_synced_at: string | null;
    sync_error: string | null;
}

interface Connection {
    shop_name: string | null;
    shop_id: string | null;
}

const props = defineProps<{
    product: ProductData;
    setting: SettingData;
    listing: ListingData | null;
    connection: Connection | null;
}>();

const { fmtCurrency } = useAdmin();
const page  = usePage();
const flash = computed(() => (page.props as any).flash ?? {});

const form = useForm({
    enabled:              props.setting.enabled,
    override_title:       props.setting.override_title,
    override_description: props.setting.override_description,
    override_price:       props.setting.override_price?.toString() ?? '',
    override_tags:        props.setting.override_tags,
});

const titleLength   = computed(() => form.override_title.length);
const titleOverflow = computed(() => titleLength.value > 140);

const tagsArray = computed(() =>
    form.override_tags
        ? form.override_tags.split(',').map(t => t.trim()).filter(Boolean)
        : []
);
const tagsOverflow = computed(() => tagsArray.value.length > 13);

const submit = () => {
    form.put(route('admin.marketplace.etsy.products.settings.save', props.product.id));
};

const etsyListingUrl = (id: string) => `https://www.etsy.com/listing/${id}`;

const listingBadgeClass = (status: string) => ({
    draft:  'adm-badge adm-badge--warn',
    active: 'adm-badge adm-badge--on',
    synced: 'adm-badge adm-badge--lav',
    error:  'adm-badge adm-badge--red',
}[status] ?? 'adm-badge adm-badge--off');

const formatDate = (iso: string | null) =>
    iso ? new Date(iso).toLocaleString('en-GB', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
</script>

<template>
    <AdminLayout>
        <Head :title="`Etsy: ${product.name} — Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.marketplace.etsy.index')">Marketplaces</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <Link :href="route('admin.marketplace.etsy.products')">Etsy Products</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ product.name }}</span>
                </div>
                <h1 class="adm-title">Etsy Settings</h1>
                <p class="adm-sub">Customise how this product appears on Etsy</p>
            </div>
        </div>

        <!-- Flash -->
        <div v-if="flash.success" class="adm-flash adm-flash--success">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="adm-flash adm-flash--error">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            {{ flash.error }}
        </div>

        <div class="adm-form-grid">

            <!-- Left: overrides form -->
            <div class="adm-form-left">

                <form @submit.prevent="submit">

                    <!-- Enable toggle card -->
                    <div class="adm-card adm-card--sm ps-enable-card">
                        <div class="ps-enable-row">
                            <div>
                                <p class="adm-label">Enable for Etsy</p>
                                <p class="adm-sub" style="margin-top:2px">Include this product in Etsy exports and syncs</p>
                            </div>
                            <button type="button" @click="form.enabled = !form.enabled"
                                :class="['ep-toggle', form.enabled ? 'ep-toggle--on' : 'ep-toggle--off']">
                                <span class="ep-toggle-dot"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="adm-card adm-card--sm">
                        <div class="adm-card-title">Listing Title</div>
                        <div class="adm-field">
                            <label class="adm-label">
                                Etsy title
                                <span class="adm-label-note">Leave blank to use product name</span>
                            </label>
                            <input v-model="form.override_title" type="text" maxlength="140"
                                :placeholder="product.name" class="adm-input"
                                :class="{ 'adm-input--err': titleOverflow }" />
                            <div class="ps-char-count" :class="{ 'ps-char-count--over': titleOverflow }">
                                {{ titleLength }} / 140
                            </div>
                            <p v-if="form.errors.override_title" class="adm-err">{{ form.errors.override_title }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="adm-card adm-card--sm">
                        <div class="adm-card-title">Listing Description</div>
                        <div class="adm-field">
                            <label class="adm-label">
                                Etsy description
                                <span class="adm-label-note">Leave blank to use product description</span>
                            </label>
                            <textarea v-model="form.override_description" rows="8"
                                placeholder="Write an Etsy-optimised description…"
                                class="adm-textarea" style="min-height:200px" />
                            <p v-if="form.errors.override_description" class="adm-err">{{ form.errors.override_description }}</p>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="adm-card adm-card--sm">
                        <div class="adm-card-title">Pricing</div>
                        <div class="adm-field">
                            <label class="adm-label">
                                Etsy price
                                <span class="adm-label-note">Leave blank to use product price ({{ fmtCurrency(product.cost) }})</span>
                            </label>
                            <div class="adm-prefix-wrap">
                                <span class="adm-prefix">£</span>
                                <input v-model="form.override_price" type="number" step="0.01" min="0"
                                    :placeholder="product.cost.toString()"
                                    class="adm-input adm-input--prefixed"
                                    :class="{ 'adm-input--err': form.errors.override_price }" />
                            </div>
                            <p v-if="form.errors.override_price" class="adm-err">{{ form.errors.override_price }}</p>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="adm-card adm-card--sm">
                        <div class="adm-card-title">Tags</div>
                        <div class="adm-field">
                            <label class="adm-label">
                                Etsy tags
                                <span class="adm-label-note">Comma-separated, max 13 tags</span>
                            </label>
                            <input v-model="form.override_tags" type="text"
                                placeholder="e.g. natural soap, vegan, gift for her"
                                class="adm-input" :class="{ 'adm-input--err': tagsOverflow }" />
                            <!-- Tag pills -->
                            <div v-if="tagsArray.length" class="ps-tag-pills">
                                <span v-for="tag in tagsArray" :key="tag" class="adm-badge adm-badge--lav">
                                    {{ tag }}
                                </span>
                            </div>
                            <p v-if="tagsOverflow" class="adm-err">Maximum 13 tags. You have {{ tagsArray.length }}.</p>
                            <p v-if="form.errors.override_tags" class="adm-err">{{ form.errors.override_tags }}</p>
                        </div>
                    </div>

                    <!-- Save -->
                    <button type="submit" :disabled="form.processing" class="adm-submit">
                        <svg v-if="form.processing" class="adm-spinner" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        {{ form.processing ? 'Saving…' : 'Save Etsy Settings' }}
                    </button>

                </form>

            </div>

            <!-- Right sidebar -->
            <div class="adm-form-right">

                <!-- Product reference card -->
                <div class="adm-card adm-card--sm adm-card--sticky">
                    <div class="adm-card-title">Product</div>

                    <img v-if="product.images[0]?.image" :src="product.images[0].image"
                        :alt="product.name" class="ps-product-img" />

                    <p class="ps-product-name">{{ product.name }}</p>
                    <p class="adm-td--mono" style="font-size:0.75rem; margin-bottom:0.75rem">{{ product.mpn }}</p>

                    <div class="ps-product-meta">
                        <div>
                            <p class="ep-stat-label">Website price</p>
                            <p class="ep-stat-val">{{ fmtCurrency(product.cost) }}</p>
                        </div>
                        <div>
                            <p class="ep-stat-label">Stock</p>
                            <p class="ep-stat-val">{{ product.stock_qty }}</p>
                        </div>
                    </div>

                    <Link :href="route('admin.products.edit', product.id)"
                        class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--full" style="margin-top:0.75rem">
                        Edit product
                    </Link>
                </div>

                <!-- Listing status card -->
                <div class="adm-card adm-card--sm">
                    <div class="adm-card-title">Etsy Listing</div>

                    <template v-if="listing">
                        <div class="ps-listing-row">
                            <span>Status</span>
                            <a :href="etsyListingUrl(listing.listing_id)" target="_blank" rel="noopener"
                                :class="listingBadgeClass(listing.status)" class="ps-listing-link">
                                {{ listing.status }}
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                            </a>
                        </div>
                        <div class="ps-listing-row">
                            <span>Listing ID</span>
                            <span class="adm-td--mono" style="font-size:0.78rem">#{{ listing.listing_id }}</span>
                        </div>
                        <div class="ps-listing-row">
                            <span>Last synced</span>
                            <span class="adm-td--mono" style="font-size:0.75rem">{{ formatDate(listing.last_synced_at) }}</span>
                        </div>
                        <div v-if="listing.sync_error" class="ps-listing-error">{{ listing.sync_error }}</div>
                        <div class="ps-listing-actions">
                            <button @click="$inertia.post(route('admin.marketplace.etsy.products.sync', product.id))"
                                class="adm-btn adm-btn--ghost adm-btn--sm adm-btn--full">
                                Sync to Etsy
                            </button>
                            <button @click="$inertia.delete(route('admin.marketplace.etsy.products.unlink', product.id))"
                                class="adm-btn adm-btn--danger adm-btn--sm adm-btn--full">
                                Unlink
                            </button>
                        </div>
                    </template>

                    <template v-else>
                        <p class="adm-sub" style="margin-bottom:0.85rem">
                            This product hasn't been exported to Etsy yet.
                        </p>
                        <button @click="$inertia.post(route('admin.marketplace.etsy.products.export', product.id))"
                            :disabled="!form.enabled"
                            class="adm-btn adm-btn--primary adm-btn--full">
                            Export to Etsy
                        </button>
                        <p v-if="!form.enabled" class="adm-err" style="margin-top:0.4rem;text-align:center">
                            Enable this product for Etsy first
                        </p>
                    </template>
                </div>

            </div>

        </div>

    </AdminLayout>
</template>

<style scoped>
/* Enable toggle card */
.ps-enable-card { border-left: 3px solid var(--bb-green); }
.ps-enable-row  {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

/* Toggle (shared with Products.vue) */
.ep-toggle {
    display: inline-flex;
    align-items: center;
    width: 42px;
    height: 24px;
    border-radius: 999px;
    border: none;
    padding: 2px;
    cursor: pointer;
    transition: background 0.2s;
    flex-shrink: 0;
}
.ep-toggle--on  { background: var(--bb-green); }
.ep-toggle--off { background: #ccc; }
.ep-toggle-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    transition: transform 0.2s;
    flex-shrink: 0;
}
.ep-toggle--on  .ep-toggle-dot { transform: translateX(18px); }
.ep-toggle--off .ep-toggle-dot { transform: translateX(0); }

/* Char counter */
.ps-char-count {
    text-align: right;
    font-size: 0.72rem;
    color: var(--bb-muted);
    margin-top: 2px;
}
.ps-char-count--over { color: var(--bb-red); font-weight: 600; }

/* Tag pills */
.ps-tag-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-top: 0.5rem;
}

/* Product sidebar */
.ps-product-img {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    border-radius: var(--bb-radius);
    border: 1px solid var(--bb-border);
    margin-bottom: 0.75rem;
}
.ps-product-name {
    font-size: 0.92rem;
    font-weight: 700;
    color: var(--bb-text);
    margin-bottom: 0.1rem;
}
.ps-product-meta {
    display: flex;
    gap: 1.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--bb-border);
}
.ep-stat-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--bb-muted); }
.ep-stat-val   { font-size: 0.9rem; font-weight: 600; color: var(--bb-text); }

/* Listing status */
.ps-listing-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    border-bottom: 1px solid var(--bb-border);
    font-size: 0.82rem;
    color: var(--bb-muted);
}
.ps-listing-row:last-of-type { border-bottom: none; }
.ps-listing-link {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    text-decoration: none;
    transition: opacity 0.12s;
}
.ps-listing-link:hover { opacity: 0.75; }
.ps-listing-error {
    font-size: 0.75rem;
    color: var(--bb-red);
    background: var(--bb-red-bg);
    border: 1px solid var(--bb-red-border);
    border-radius: var(--bb-radius-sm);
    padding: 0.4rem 0.6rem;
    margin-top: 0.5rem;
}
.ps-listing-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.85rem;
}
</style>
