<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { debounce } from 'lodash';
import { useAdmin } from '@/composables/useAdmin';

interface Category { id: number; name: string; }
interface Courier { id: number; name: string; type: string; status: string; cost: number; }
interface ParentProduct { id: number; name: string; }
interface ProductImage { id: number; product_id: number; image: string; status: string; file_path: string; is_enabled: boolean; }
interface Product {
    id: number; mpn: string; name: string; description: string;
    status: 'enabled' | 'disabled'; cost: number; stock_qty: number;
    details: string; parent_product_id: number | null;
    how_to_use: string | null;
    seo: { meta_title: string; meta_description: string; slug: string; };
}
interface Oil { id: number; name: string; supplier: string | null; cas_primary: string | null; }
interface ProductMaterial { oil_id: number; percentage: string; }
interface FaqItem { question: string; answer: string; }

const props = defineProps<{
    product?: Product;
    categories: Category[];
    couriers: Courier[];
    parentProducts: ParentProduct[];
    selectedCategoryIds: number[];
    selectedCourierId: number | null;
    courierPerItem: string;
    oils: Oil[];
    productMaterials: ProductMaterial[];
    productFaqs: FaqItem[];         // comes from its own DB table now
    productImages: ProductImage[];
    isEditing: boolean;
    errors: Record<string, string>;
}>();

const { fmtSize } = useAdmin();

const form = useForm({
    mpn: props.product?.mpn || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    details: props.product?.details || '',
    how_to_use: props.product?.how_to_use || '',
    faqs: (props.productFaqs ?? []) as FaqItem[],
    status: props.product?.status || 'enabled',
    cost: props.product?.cost?.toString() || '0.00',
    stock_qty: props.product?.stock_qty || 0,
    parent_product_id: props.product?.parent_product_id || null,
    category_ids: props.selectedCategoryIds || ([] as number[]),
    courier_id: props.selectedCourierId || null,
    courier_per_item: props.courierPerItem || 'no',
    materials: (props.productMaterials ?? []).map(m => ({ oil_id: m.oil_id, percentage: m.percentage })) as ProductMaterial[],
    meta_title: props.product?.seo?.meta_title || '',
    meta_description: props.product?.seo?.meta_description || '',
    slug: props.product?.seo?.slug || '',
    new_images: [] as File[],
    images_to_delete: [] as number[],
    images_to_toggle: [] as number[],
});

const title = computed(() => props.isEditing ? `Edit: ${props.product?.name}` : 'New Product');
const submitLabel = computed(() => props.isEditing ? 'Save Changes' : 'Create Product');

const filteredExistingImages = computed(() =>
    props.productImages
        .filter(img => !form.images_to_delete.includes(img.id))
        .map(img => form.images_to_toggle.includes(img.id) ? { ...img, is_enabled: !img.is_enabled } : img)
);

watch(() => form.name, debounce((n: string) => {
    if (form.processing) return;
    const slug = n.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    if (!form.slug || form.slug === props.product?.seo?.slug) form.slug = slug;
    if (!form.meta_title || form.meta_title === props.product?.seo?.meta_title) form.meta_title = n;
}, 500));

// ── Image helpers ──────────────────────────────────────────────────────────
const handleFileUpload = (e: Event) => {
    const t = e.target as HTMLInputElement;
    if (t.files) {
        Array.from(t.files).forEach(f => { if (form.new_images.length < 5) form.new_images.push(f); });
        t.value = '';
    }
};
const removeNewImage = (i: number) => form.new_images.splice(i, 1);
const toggleImageStatus = (id: number) => {
    const idx = form.images_to_toggle.indexOf(id);
    idx === -1 ? form.images_to_toggle.push(id) : form.images_to_toggle.splice(idx, 1);
};
const deleteExistingImage = (id: number) => {
    if (!form.images_to_delete.includes(id)) form.images_to_delete.push(id);
    const ti = form.images_to_toggle.indexOf(id);
    if (ti !== -1) form.images_to_toggle.splice(ti, 1);
};

// ── Category / courier ─────────────────────────────────────────────────────
const handleCategoryChange = (id: number, checked: boolean) => {
    checked ? (!form.category_ids.includes(id) && form.category_ids.push(id))
        : (form.category_ids = form.category_ids.filter(c => c !== id));
};
const handleCourierChange = (id: number, checked: boolean) => {
    form.courier_id = checked ? id : null;
    form.courier_per_item = 'no';
};

// ── FAQ helpers ────────────────────────────────────────────────────────────
const addFaq = () => form.faqs.push({ question: '', answer: '' });
const removeFaq = (i: number) => form.faqs.splice(i, 1);

// ── Oil formulation ────────────────────────────────────────────────────────
const addMaterial = () => form.materials.push({ oil_id: 0, percentage: '' });
const removeMaterial = (i: number) => form.materials.splice(i, 1);
const availableOilsFor = (idx: number) => {
    const taken = form.materials.map((m, i) => i !== idx ? m.oil_id : null).filter(id => id !== null && id !== 0) as number[];
    return props.oils.filter(o => !taken.includes(o.id));
};
const materialsTotal = computed(() => form.materials.reduce((s, m) => s + (parseFloat(m.percentage) || 0), 0));

// ── Submit ─────────────────────────────────────────────────────────────────
const submit = () => {
    (form.data() as any).cost = parseFloat(form.cost);
    if (props.isEditing && props.product) {
        form.transform(d => ({ ...d, _method: 'put' }))
            .post(route('admin.products.update', props.product.id), {
                preserveScroll: true,
                onSuccess: () => { form.new_images = []; form.images_to_delete = []; form.images_to_toggle = []; },
            });
    } else {
        form.post(route('admin.products.store'), {
            preserveScroll: true,
            onSuccess: () => { form.new_images = []; form.images_to_delete = []; form.images_to_toggle = []; },
        });
    }
};
</script>

<template>
    <AdminLayout>

        <Head :title="`${title} — Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.products.index')" class="adm-breadcrumb a">Products</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="adm-title">{{ title }}</h1>
                <p class="adm-sub">{{ isEditing ? 'Update details, inventory, images and SEO.' :
                    'Fill in the details for a new product.' }}</p>
            </div>
            <div v-if="form.isDirty" class="adm-unsaved">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                Unsaved changes
            </div>
        </div>

        <form @submit.prevent="submit" class="adm-form-grid">

            <!-- ── Left column ── -->
            <div class="adm-form-left">

                <!-- General info -->
                <section class="adm-card">
                    <h2 class="adm-card-title">General Information</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <label class="adm-label" for="name">Product Name</label>
                            <input id="name" type="text" v-model="form.name" required class="adm-input"
                                :class="{ 'adm-input--err': form.errors.name }" />
                            <p v-if="form.errors.name" class="adm-err">{{ form.errors.name }}</p>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label" for="mpn">MPN / SKU</label>
                            <input id="mpn" type="text" v-model="form.mpn" required class="adm-input"
                                :class="{ 'adm-input--err': form.errors.mpn }" />
                            <p v-if="form.errors.mpn" class="adm-err">{{ form.errors.mpn }}</p>
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label" for="parent">
                            Parent Product
                            <span class="adm-label-note">(for variations)</span>
                        </label>
                        <select id="parent" v-model="form.parent_product_id" class="adm-select">
                            <option :value="null">No parent — standalone product</option>
                            <option v-for="p in parentProducts" :key="p.id" :value="p.id"
                                :disabled="isEditing && p.id === product?.id">
                                {{ p.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.parent_product_id" class="adm-err">{{ form.errors.parent_product_id }}</p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label" for="description">Description</label>
                        <textarea id="description" v-model="form.description" required rows="7" class="adm-textarea"
                            :class="{ 'adm-input--err': form.errors.description }"></textarea>
                        <p v-if="form.errors.description" class="adm-err">{{ form.errors.description }}</p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label" for="details">Details</label>
                        <textarea id="details" v-model="form.details" required rows="7" class="adm-textarea"
                            :class="{ 'adm-input--err': form.errors.details }"></textarea>
                        <p v-if="form.errors.details" class="adm-err">{{ form.errors.details }}</p>
                    </div>
                </section>

                <!-- Pricing & inventory -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Pricing &amp; Inventory</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <label class="adm-label" for="cost">Unit Cost (excl. VAT)</label>
                            <div class="adm-prefix-wrap">
                                <span class="adm-prefix">£</span>
                                <input id="cost" type="number" v-model="form.cost" required min="0.01" step="0.01"
                                    class="adm-input adm-input--prefixed"
                                    :class="{ 'adm-input--err': form.errors.cost }" />
                            </div>
                            <p v-if="form.errors.cost" class="adm-err">{{ form.errors.cost }}</p>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label" for="stock">Stock Quantity</label>
                            <input id="stock" type="number" v-model="form.stock_qty" required min="0" step="1"
                                class="adm-input" :class="{ 'adm-input--err': form.errors.stock_qty }" />
                            <p v-if="form.errors.stock_qty" class="adm-err">{{ form.errors.stock_qty }}</p>
                        </div>
                    </div>
                </section>

                <!-- Images -->
                <section class="adm-card">
                    <h2 class="adm-card-title">
                        Product Images
                        <span class="adm-card-title-note">max 5 total</span>
                    </h2>

                    <label for="file-upload" class="adm-upload-zone">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <span>Click to upload or drag &amp; drop</span>
                        <span class="adm-upload-note">JPEG, PNG, WebP — max 2 MB each</span>
                        <input type="file" id="file-upload" multiple accept="image/jpeg,image/png,image/webp"
                            @change="handleFileUpload" style="display:none" />
                    </label>
                    <p v-if="form.errors['new_images']" class="adm-err">{{ form.errors['new_images'] }}</p>

                    <!-- Queued new images -->
                    <div v-if="form.new_images.length" class="pe-queue">
                        <p class="pe-sub-label">Queued for upload ({{ form.new_images.length }})</p>
                        <div class="pe-queue-list">
                            <div v-for="(file, i) in form.new_images" :key="i" class="pe-queue-item">
                                <div class="pe-queue-thumb">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                </div>
                                <div class="pe-queue-info">
                                    <p class="pe-queue-name">{{ file.name }}</p>
                                    <p class="pe-queue-size">{{ fmtSize(file.size) }}</p>
                                </div>
                                <button type="button" @click="removeNewImage(i)" class="pe-icon-remove"
                                    aria-label="Remove">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6 6 18M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Existing images -->
                    <div v-if="filteredExistingImages.length">
                        <p class="pe-sub-label">Existing images ({{ filteredExistingImages.length }})</p>
                        <div class="pe-img-grid">
                            <div v-for="img in filteredExistingImages" :key="img.id" class="pe-img-card"
                                :class="{ 'pe-img-card--disabled': !img.is_enabled }">
                                <img :src="img.file_path" :alt="`Image ${img.id}`" class="pe-img-thumb" />
                                <div v-if="!img.is_enabled" class="pe-img-hidden-tag">Hidden</div>
                                <div class="pe-img-actions">
                                    <button type="button" @click="toggleImageStatus(img.id)" class="pe-img-btn"
                                        :class="img.is_enabled ? 'pe-img-btn--hide' : 'pe-img-btn--show'"
                                        :title="img.is_enabled ? 'Hide' : 'Show'">
                                        <svg v-if="img.is_enabled" width="12" height="12" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path
                                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                                            <path
                                                d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                                            <line x1="1" y1="1" x2="23" y2="23" />
                                        </svg>
                                        <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="deleteExistingImage(img.id)"
                                        class="pe-img-btn pe-img-btn--del" title="Delete">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── How to Use ── -->
                <section class="adm-card">
                    <h2 class="adm-card-title">
                        How to Use
                        <span class="adm-card-title-note">shown on product page</span>
                    </h2>
                    <p class="pe-hint">Write instructions for the customer. Supports basic HTML — paragraphs, lists,
                        bold text.</p>
                    <div class="adm-field">
                        <textarea v-model="form.how_to_use" rows="6" class="adm-textarea pe-mono"
                            placeholder="<p>Apply a few drops to your wrists...</p>&#10;<ul>&#10;  <li>Step 1: ...</li>&#10;</ul>"></textarea>
                        <p v-if="form.errors.how_to_use" class="adm-err">{{ form.errors.how_to_use }}</p>
                    </div>
                    <!-- Live preview -->
                    <div v-if="form.how_to_use?.trim()" class="pe-preview">
                        <p class="pe-preview-label">Preview</p>
                        <div class="pe-preview-body" v-html="form.how_to_use"></div>
                    </div>
                </section>

                <!-- ── FAQ ── -->
                <section class="adm-card">
                    <h2 class="adm-card-title">
                        FAQs
                        <span class="adm-card-title-note">shown on product page</span>
                    </h2>
                    <p class="pe-hint">Add product-specific questions. These display as a collapsible accordion on the
                        product page.</p>

                    <div class="pe-faq-list">
                        <div v-for="(faq, i) in form.faqs" :key="i" class="pe-faq-item">
                            <div class="pe-faq-num">{{ i + 1 }}</div>
                            <div class="pe-faq-fields">
                                <div class="adm-field">
                                    <label class="adm-label"
                                        style="font-size:0.7rem; letter-spacing:0.07em; text-transform:uppercase; color:var(--bb-muted)">Question</label>
                                    <input type="text" v-model="faq.question" class="adm-input adm-input--sm"
                                        placeholder="e.g. How long does the scent last?" />
                                </div>
                                <div class="adm-field">
                                    <label class="adm-label"
                                        style="font-size:0.7rem; letter-spacing:0.07em; text-transform:uppercase; color:var(--bb-muted)">Answer</label>
                                    <textarea v-model="faq.answer" rows="3" class="adm-textarea adm-input--sm"
                                        placeholder="e.g. Our fragrances typically last 6–8 hours on skin..."></textarea>
                                </div>
                            </div>
                            <button type="button" @click="removeFaq(i)" class="pe-icon-remove" aria-label="Remove FAQ">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.faqs.length === 0" class="pe-empty-note">No FAQs added yet.</p>
                    </div>

                    <button type="button" @click="addFaq" class="pe-dashed-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Add FAQ
                    </button>
                </section>

                <!-- SEO -->
                <section class="adm-card">
                    <h2 class="adm-card-title">SEO &amp; URL</h2>
                    <div class="adm-field">
                        <label class="adm-label" for="meta_title">Meta Title</label>
                        <input id="meta_title" type="text" v-model="form.meta_title" maxlength="255" class="adm-input"
                            :class="{ 'adm-input--err': form.errors.meta_title }" />
                        <p v-if="form.errors.meta_title" class="adm-err">{{ form.errors.meta_title }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="meta_desc">
                            Meta Description
                            <span class="adm-label-note">max 500 chars</span>
                        </label>
                        <textarea id="meta_desc" v-model="form.meta_description" rows="3" maxlength="500"
                            class="adm-textarea" :class="{ 'adm-input--err': form.errors.meta_description }"></textarea>
                        <p v-if="form.errors.meta_description" class="adm-err">{{ form.errors.meta_description }}</p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="slug">URL Slug</label>
                        <div class="adm-slug-wrap">
                            <span class="adm-slug-prefix">/product/</span>
                            <input id="slug" type="text" v-model="form.slug" required class="adm-input adm-slug-input"
                                :class="{ 'adm-input--err': form.errors.slug }" />
                        </div>
                        <p v-if="form.errors.slug" class="adm-err">{{ form.errors.slug }}</p>
                    </div>
                </section>

            </div>

            <!-- ── Right column ── -->
            <div class="adm-form-right">

                <!-- Status & submit — sticky on desktop only -->
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Status &amp; Actions</h2>
                    <div class="adm-field">
                        <label class="adm-label">Product Status</label>
                        <div class="adm-status-btns">
                            <button type="button" @click="form.status = 'enabled'" class="adm-status-btn"
                                :class="{ 'adm-status-btn--on': form.status === 'enabled' }">
                                <span class="adm-status-dot adm-status-dot--green"></span>
                                Active
                            </button>
                            <button type="button" @click="form.status = 'disabled'" class="adm-status-btn"
                                :class="{ 'adm-status-btn--off': form.status === 'disabled' }">
                                <span class="adm-status-dot adm-status-dot--grey"></span>
                                Inactive
                            </button>
                        </div>
                        <p v-if="form.errors.status" class="adm-err">{{ form.errors.status }}</p>
                    </div>
                    <button type="submit" :disabled="form.processing" class="adm-submit">
                        <svg v-if="form.processing" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ form.processing ? 'Saving...' : submitLabel }}
                    </button>
                    <p v-if="form.isDirty" class="adm-unsaved-inline">Unsaved changes</p>
                </section>

                <!-- Categories -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Categories</h2>
                    <div class="adm-check-list">
                        <label v-for="cat in categories" :key="cat.id" class="adm-check-item"
                            :class="{ 'adm-check-item--active': form.category_ids.includes(cat.id) }">
                            <input type="checkbox" :checked="form.category_ids.includes(cat.id)"
                                @change="handleCategoryChange(cat.id, ($event.target as HTMLInputElement).checked)"
                                class="adm-checkbox" />
                            {{ cat.name }}
                        </label>
                    </div>
                    <p v-if="form.errors.category_ids" class="adm-err">{{ form.errors.category_ids }}</p>
                </section>

                <!-- Couriers -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Courier</h2>
                    <div class="adm-check-list">
                        <label v-for="c in couriers" :key="c.id" class="adm-check-item pe-courier-item"
                            :class="{ 'adm-check-item--active': form.courier_id === c.id }">
                            <div class="pe-courier-row">
                                <input type="radio" name="courier" :value="c.id" :checked="form.courier_id === c.id"
                                    @change="handleCourierChange(c.id, true)" class="adm-checkbox" />
                                <span class="pe-courier-name">{{ c.type }} — {{ c.name }}</span>
                                <span class="pe-courier-cost">£{{ c.cost }}</span>
                            </div>
                            <div v-if="form.courier_id === c.id" class="pe-courier-per-item">
                                <span class="adm-label" style="font-size:0.7rem">Per item?</span>
                                <select v-model="form.courier_per_item" class="adm-select adm-input--sm">
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                        </label>
                        <label class="adm-check-item" :class="{ 'adm-check-item--active': form.courier_id === null }">
                            <input type="radio" name="courier" :checked="form.courier_id === null"
                                @change="handleCourierChange(0, false)" class="adm-checkbox" />
                            No courier
                        </label>
                    </div>
                </section>

                <!-- Fragrance formulation -->
                <section class="adm-card">
                    <h2 class="adm-card-title">
                        Fragrance Formulation
                        <span class="adm-card-title-note">for CLP</span>
                    </h2>
                    <div class="pe-total-bar" :class="materialsTotal > 100 ? 'pe-total-bar--over' : 'pe-total-bar--ok'">
                        <span>Total</span>
                        <strong>{{ materialsTotal.toFixed(2) }}%</strong>
                    </div>
                    <div class="pe-materials">
                        <div v-for="(mat, i) in form.materials" :key="i" class="pe-material-row">
                            <select v-model="mat.oil_id" class="adm-select adm-input--sm pe-material-select">
                                <option :value="0" disabled>Select oil…</option>
                                <option v-for="oil in availableOilsFor(i)" :key="oil.id" :value="oil.id">
                                    {{ oil.name }}{{ oil.supplier ? ` (${oil.supplier})` : '' }}
                                </option>
                                <option v-if="mat.oil_id !== 0 && !availableOilsFor(i).find(o => o.id === mat.oil_id)"
                                    :value="mat.oil_id">
                                    {{oils.find(o => o.id === mat.oil_id)?.name}}
                                </option>
                            </select>
                            <div class="pe-pct-wrap">
                                <input type="number" v-model="mat.percentage" min="0.01" max="100" step="0.01"
                                    placeholder="0.00" class="adm-input adm-input--sm pe-pct-input" />
                                <span class="pe-pct-symbol">%</span>
                            </div>
                            <button type="button" @click="removeMaterial(i)" class="pe-icon-del" aria-label="Remove">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.materials.length === 0" class="pe-empty-note">No oils linked yet.</p>
                        <p v-if="form.errors.materials" class="adm-err">{{ form.errors.materials }}</p>
                    </div>
                    <button type="button" @click="addMaterial" :disabled="form.materials.length >= oils.length"
                        class="pe-dashed-btn">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Add oil
                    </button>
                </section>

            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
/*
 * Page-specific styles — prefix pe- (product edit)
 * All shared styles come from admin-design-system.css
 */

/* ── Image queue ── */
.pe-queue {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.pe-sub-label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bb-muted);
    margin-bottom: 0.35rem;
}

.pe-queue-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.pe-queue-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: var(--bb-radius);
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
}

.pe-queue-thumb {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #e8e4f0;
    color: var(--bb-lav-d);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pe-queue-info {
    flex: 1;
    min-width: 0;
}

.pe-queue-name {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--bb-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pe-queue-size {
    font-size: 0.7rem;
    color: var(--bb-muted);
}

/* ── Shared small icon buttons ── */
.pe-icon-remove {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: none;
    color: var(--bb-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background 0.15s, color 0.15s;
}

.pe-icon-remove:hover {
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

/* ── Existing images ── */
.pe-img-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
    margin-top: 0.5rem;
}

@media (max-width: 640px) {
    .pe-img-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.pe-img-card {
    position: relative;
    border-radius: var(--bb-radius);
    border: 1px solid var(--bb-border);
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--bb-cream);
    transition: opacity 0.15s;
}

.pe-img-card--disabled {
    opacity: 0.5;
}

.pe-img-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.pe-img-hidden-tag {
    position: absolute;
    top: 0;
    left: 0;
    background: rgba(224, 92, 110, 0.85);
    color: #fff;
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 2px 5px;
}

.pe-img-actions {
    position: absolute;
    bottom: 0;
    right: 0;
    display: flex;
    gap: 2px;
    padding: 3px;
    background: rgba(26, 26, 46, 0.55);
    border-top-left-radius: 6px;
}

.pe-img-btn {
    width: 22px;
    height: 22px;
    border-radius: 4px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #fff;
    transition: opacity 0.15s;
}

.pe-img-btn:hover {
    opacity: 0.8;
}

.pe-img-btn--hide {
    background: #e0963a;
}

.pe-img-btn--show {
    background: var(--bb-green);
}

.pe-img-btn--del {
    background: var(--bb-red);
}

/* ── Section hints ── */
.pe-hint {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-style: italic;
    line-height: 1.5;
    margin-top: -0.25rem;
}

/* Monospace textarea for HTML */
.pe-mono {
    font-family: var(--bb-mono, monospace);
    font-size: 0.82rem;
    line-height: 1.65;
}

/* Live preview */
.pe-preview {
    border: 1px solid var(--bb-border);
    border-radius: var(--bb-radius);
    background: var(--bb-cream);
    overflow: hidden;
}

.pe-preview-label {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bb-muted);
    padding: 0.4rem 0.85rem;
    background: var(--bb-surface);
    border-bottom: 1px solid var(--bb-border);
}

.pe-preview-body {
    padding: 0.85rem 1rem;
    font-size: 0.88rem;
    color: var(--bb-text);
    line-height: 1.7;
}

.pe-preview-body :deep(p) {
    margin-bottom: 0.65rem;
}

.pe-preview-body :deep(ul),
.pe-preview-body :deep(ol) {
    padding-left: 1.25rem;
    margin-bottom: 0.65rem;
}

.pe-preview-body :deep(li) {
    margin-bottom: 0.25rem;
}

.pe-preview-body :deep(strong) {
    font-weight: 700;
}

/* ── FAQ ── */
.pe-faq-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.pe-faq-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem;
    border-radius: var(--bb-radius);
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
}

.pe-faq-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    flex-shrink: 0;
    background: #f0edf8;
    color: var(--bb-lav-d);
    font-size: 0.68rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
}

.pe-faq-fields {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.pe-empty-note {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-style: italic;
}

/* ── Dashed add button (shared for FAQ and materials) ── */
.pe-dashed-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    width: 100%;
    padding: 0.55rem;
    border-radius: var(--bb-radius);
    border: 1.5px dashed var(--bb-border);
    background: none;
    color: var(--bb-muted);
    font-family: var(--bb-font);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.pe-dashed-btn:hover:not(:disabled) {
    border-color: var(--bb-lav-d);
    color: var(--bb-lav-d);
    background: #faf8ff;
}

.pe-dashed-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Courier ── */
.pe-courier-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.4rem;
}

.pe-courier-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    width: 100%;
}

.pe-courier-name {
    flex: 1;
    font-size: 0.85rem;
    font-weight: 500;
}

.pe-courier-cost {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-weight: 600;
}

.pe-courier-per-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-left: 1.6rem;
}

/* ── Fragrance formulation ── */
.pe-total-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.85rem;
    border-radius: var(--bb-radius);
    font-size: 0.82rem;
}

.pe-total-bar--ok {
    background: var(--bb-green-bg);
    color: #2a7a50;
    border: 1px solid var(--bb-green-border);
}

.pe-total-bar--over {
    background: var(--bb-red-bg);
    color: var(--bb-red);
    border: 1px solid var(--bb-red-border);
}

.pe-materials {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.pe-material-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pe-material-select {
    flex: 1;
}

.pe-pct-wrap {
    position: relative;
    width: 80px;
    flex-shrink: 0;
}

.pe-pct-input {
    padding-right: 1.5rem;
    width: 100%;
}

.pe-pct-symbol {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.8rem;
    color: var(--bb-muted);
    pointer-events: none;
}

.pe-icon-del {
    width: 28px;
    height: 28px;
    border-radius: var(--bb-radius-sm);
    border: 1px solid var(--bb-border);
    background: none;
    color: var(--bb-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.12s, color 0.12s, border-color 0.12s;
}

.pe-icon-del:hover {
    background: var(--bb-red-bg);
    color: var(--bb-red);
    border-color: var(--bb-red);
}
</style>
