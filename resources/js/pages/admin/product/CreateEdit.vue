<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { debounce } from 'lodash';

interface Category { id: number; name: string; }
interface Courier { id: number; name: string; type: string; status: string; cost: number; }
interface ParentProduct { id: number; name: string; }
interface ProductImage { id: number; product_id: number; image: string; status: string; file_path: string; is_enabled: boolean; }
interface Product {
    id: number; mpn: string; name: string; description: string;
    status: 'enabled' | 'disabled'; cost: number; stock_qty: number;
    parent_product_id: number | null;
    seo: { meta_title: string; meta_description: string; slug: string; };
}
interface Oil { id: number; name: string; supplier: string | null; cas_primary: string | null; }
interface ProductMaterial { oil_id: number; percentage: string; }

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
    productImages: ProductImage[];
    isEditing: boolean;
    errors: Record<string, string>;
}>();

const form = useForm({
    mpn: props.product?.mpn || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    status: props.product?.status || 'enabled',
    cost: props.product?.cost.toString() || '0.00',
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
        .map(img => form.images_to_toggle.includes(img.id)
            ? { ...img, is_enabled: !img.is_enabled }
            : img
        )
);

watch(() => form.name, debounce((n: string) => {
    if (form.processing) return;
    const slug = n.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    if (!form.slug || form.slug === props.product?.seo?.slug) form.slug = slug;
    if (!form.meta_title || form.meta_title === props.product?.seo?.meta_title) form.meta_title = n;
}, 500));

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
const handleCategoryChange = (id: number, checked: boolean) => {
    checked
        ? !form.category_ids.includes(id) && form.category_ids.push(id)
        : (form.category_ids = form.category_ids.filter(c => c !== id));
};
const handleCourierChange = (id: number, checked: boolean) => {
    form.courier_id = checked ? id : null;
    form.courier_per_item = 'no';
};
const addMaterial = () => form.materials.push({ oil_id: 0, percentage: '' });
const removeMaterial = (i: number) => form.materials.splice(i, 1);
const availableOilsFor = (idx: number): Oil[] => {
    const taken = form.materials.map((m, i) => i !== idx ? m.oil_id : null).filter(id => id !== null && id !== 0) as number[];
    return props.oils.filter(o => !taken.includes(o.id));
};
const materialsTotal = computed(() => form.materials.reduce((s, m) => s + (parseFloat(m.percentage) || 0), 0));

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

const fmtSize = (b: number) => {
    if (!b) return '0 B';
    const i = Math.floor(Math.log(b) / Math.log(1024));
    return `${(b / Math.pow(1024, i)).toFixed(1)} ${['B', 'KB', 'MB'][i]}`;
};
</script>

<template>
    <AdminLayout>

        <Head :title="`${title} — Admin`" />

        <!-- Page header -->
        <div class="ce-header">
            <div>
                <div class="ce-breadcrumb">
                    <Link :href="route('admin.products.index')" class="ce-breadcrumb-link">Products</Link>
                    <span class="ce-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="ce-title">{{ title }}</h1>
                <p class="ce-sub">{{ isEditing ? 'Update details, inventory, images and SEO.' :
                    'Fill in the details for a new product.' }}</p>
            </div>
            <div v-if="form.isDirty" class="ce-unsaved">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 8v4M12 16h.01" />
                </svg>
                Unsaved changes
            </div>
        </div>

        <form @submit.prevent="submit" class="ce-grid">

            <!-- ── Left column ── -->
            <div class="ce-left">

                <!-- General info -->
                <section class="ce-card">
                    <h2 class="ce-card-title">General Information</h2>
                    <div class="ce-field-row">
                        <div class="ce-field">
                            <label class="ce-label" for="name">Product Name</label>
                            <input id="name" type="text" v-model="form.name" required class="ce-input"
                                :class="{ 'ce-input--err': form.errors.name }" />
                            <p v-if="form.errors.name" class="ce-err">{{ form.errors.name }}</p>
                        </div>
                        <div class="ce-field">
                            <label class="ce-label" for="mpn">MPN / SKU</label>
                            <input id="mpn" type="text" v-model="form.mpn" required class="ce-input"
                                :class="{ 'ce-input--err': form.errors.mpn }" />
                            <p v-if="form.errors.mpn" class="ce-err">{{ form.errors.mpn }}</p>
                        </div>
                    </div>

                    <div class="ce-field">
                        <label class="ce-label" for="parent">Parent Product
                            <span class="ce-label-note">(for variations)</span>
                        </label>
                        <select id="parent" v-model="form.parent_product_id" class="ce-input">
                            <option :value="null">No parent — standalone product</option>
                            <option v-for="p in parentProducts" :key="p.id" :value="p.id"
                                :disabled="isEditing && p.id === product?.id">
                                {{ p.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.parent_product_id" class="ce-err">{{ form.errors.parent_product_id }}</p>
                    </div>

                    <div class="ce-field">
                        <label class="ce-label" for="description">Description</label>
                        <textarea id="description" v-model="form.description" required rows="7"
                            class="ce-input ce-textarea"
                            :class="{ 'ce-input--err': form.errors.description }"></textarea>
                        <p v-if="form.errors.description" class="ce-err">{{ form.errors.description }}</p>
                    </div>
                </section>

                <!-- Pricing & inventory -->
                <section class="ce-card">
                    <h2 class="ce-card-title">Pricing &amp; Inventory</h2>
                    <div class="ce-field-row">
                        <div class="ce-field">
                            <label class="ce-label" for="cost">Unit Cost (excl. VAT)</label>
                            <div class="ce-input-prefix-wrap">
                                <span class="ce-input-prefix">£</span>
                                <input id="cost" type="number" v-model="form.cost" required min="0.01" step="0.01"
                                    class="ce-input ce-input--prefixed"
                                    :class="{ 'ce-input--err': form.errors.cost }" />
                            </div>
                            <p v-if="form.errors.cost" class="ce-err">{{ form.errors.cost }}</p>
                        </div>
                        <div class="ce-field">
                            <label class="ce-label" for="stock">Stock Quantity</label>
                            <input id="stock" type="number" v-model="form.stock_qty" required min="0" step="1"
                                class="ce-input" :class="{ 'ce-input--err': form.errors.stock_qty }" />
                            <p v-if="form.errors.stock_qty" class="ce-err">{{ form.errors.stock_qty }}</p>
                        </div>
                    </div>
                </section>

                <!-- Images -->
                <section class="ce-card">
                    <h2 class="ce-card-title">
                        Product Images
                        <span class="ce-card-title-note">max 5 total</span>
                    </h2>

                    <!-- Upload zone -->
                    <label for="file-upload" class="ce-upload-zone">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        <span>Click to upload or drag &amp; drop</span>
                        <span class="ce-upload-note">JPEG, PNG, WebP — max 2 MB each</span>
                        <input type="file" id="file-upload" multiple accept="image/jpeg,image/png,image/webp"
                            @change="handleFileUpload" class="ce-upload-input" />
                    </label>
                    <p v-if="form.errors['new_images']" class="ce-err">{{ form.errors['new_images'] }}</p>

                    <!-- Queued new images -->
                    <div v-if="form.new_images.length" class="ce-queue">
                        <p class="ce-queue-title">Queued for upload ({{ form.new_images.length }})</p>
                        <div class="ce-queue-list">
                            <div v-for="(file, i) in form.new_images" :key="i" class="ce-queue-item">
                                <div class="ce-queue-thumb">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="18" height="18" rx="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                </div>
                                <div class="ce-queue-info">
                                    <p class="ce-queue-name">{{ file.name }}</p>
                                    <p class="ce-queue-size">{{ fmtSize(file.size) }}</p>
                                </div>
                                <button type="button" @click="removeNewImage(i)" class="ce-queue-remove"
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
                    <div v-if="filteredExistingImages.length" class="ce-existing-imgs">
                        <p class="ce-queue-title">Existing images ({{ filteredExistingImages.length }})</p>
                        <div class="ce-img-grid">
                            <div v-for="img in filteredExistingImages" :key="img.id" class="ce-img-card"
                                :class="{ 'ce-img-card--disabled': !img.is_enabled }">
                                <img :src="img.file_path" :alt="`Image ${img.id}`" class="ce-img-thumb" />
                                <div v-if="!img.is_enabled" class="ce-img-disabled-label">Hidden</div>
                                <div class="ce-img-actions">
                                    <button type="button" @click="toggleImageStatus(img.id)" class="ce-img-btn"
                                        :class="img.is_enabled ? 'ce-img-btn--hide' : 'ce-img-btn--show'"
                                        :title="img.is_enabled ? 'Hide image' : 'Show image'">
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
                                        class="ce-img-btn ce-img-btn--del" title="Delete image">
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

                <!-- SEO -->
                <section class="ce-card">
                    <h2 class="ce-card-title">SEO &amp; URL</h2>
                    <div class="ce-field">
                        <label class="ce-label" for="meta_title">Meta Title</label>
                        <input id="meta_title" type="text" v-model="form.meta_title" maxlength="255" class="ce-input"
                            :class="{ 'ce-input--err': form.errors.meta_title }" />
                        <p v-if="form.errors.meta_title" class="ce-err">{{ form.errors.meta_title }}</p>
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" for="meta_desc">Meta Description
                            <span class="ce-label-note">max 500 chars</span>
                        </label>
                        <textarea id="meta_desc" v-model="form.meta_description" rows="3" maxlength="500"
                            class="ce-input ce-textarea"
                            :class="{ 'ce-input--err': form.errors.meta_description }"></textarea>
                        <p v-if="form.errors.meta_description" class="ce-err">{{ form.errors.meta_description }}</p>
                    </div>
                    <div class="ce-field">
                        <label class="ce-label" for="slug">URL Slug</label>
                        <div class="ce-slug-wrap">
                            <span class="ce-slug-prefix">/product/</span>
                            <input id="slug" type="text" v-model="form.slug" required class="ce-input ce-slug-input"
                                :class="{ 'ce-input--err': form.errors.slug }" />
                        </div>
                        <p v-if="form.errors.slug" class="ce-err">{{ form.errors.slug }}</p>
                    </div>
                </section>

            </div>

            <!-- ── Right column ── -->
            <div class="ce-right">

                <!-- Status & submit -->
                <section class="ce-card ce-card--sticky">
                    <h2 class="ce-card-title">Status &amp; Actions</h2>

                    <div class="ce-field">
                        <label class="ce-label" for="status">Product Status</label>
                        <div class="ce-status-btns">
                            <button type="button" @click="form.status = 'enabled'" class="ce-status-btn"
                                :class="{ 'ce-status-btn--on': form.status === 'enabled' }">
                                <span class="ce-status-dot ce-status-dot--green"></span>
                                Active
                            </button>
                            <button type="button" @click="form.status = 'disabled'" class="ce-status-btn"
                                :class="{ 'ce-status-btn--off': form.status === 'disabled' }">
                                <span class="ce-status-dot ce-status-dot--grey"></span>
                                Inactive
                            </button>
                        </div>
                        <p v-if="form.errors.status" class="ce-err">{{ form.errors.status }}</p>
                    </div>

                    <button type="submit" :disabled="form.processing" class="ce-submit-btn">
                        <svg v-if="form.processing" class="ce-spinner" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ form.processing ? 'Saving...' : submitLabel }}
                    </button>

                    <div v-if="form.isDirty" class="ce-unsaved-inline">Unsaved changes</div>
                </section>

                <!-- Categories -->
                <section class="ce-card">
                    <h2 class="ce-card-title">Categories</h2>
                    <div class="ce-check-list">
                        <label v-for="cat in categories" :key="cat.id" class="ce-check-item"
                            :class="{ 'ce-check-item--active': form.category_ids.includes(cat.id) }">
                            <input type="checkbox" :checked="form.category_ids.includes(cat.id)"
                                @change="handleCategoryChange(cat.id, ($event.target as HTMLInputElement).checked)"
                                class="ce-checkbox" />
                            {{ cat.name }}
                        </label>
                    </div>
                    <p v-if="form.errors.category_ids" class="ce-err">{{ form.errors.category_ids }}</p>
                </section>

                <!-- Couriers -->
                <section class="ce-card">
                    <h2 class="ce-card-title">Courier</h2>
                    <div class="ce-check-list">
                        <label v-for="c in couriers" :key="c.id" class="ce-check-item ce-check-item--courier"
                            :class="{ 'ce-check-item--active': form.courier_id === c.id }">
                            <input type="radio" name="courier" :value="c.id" :checked="form.courier_id === c.id"
                                @change="handleCourierChange(c.id, true)" class="ce-checkbox" />
                            <div class="ce-courier-info">
                                <span class="ce-courier-name">{{ c.type }} — {{ c.name }}</span>
                                <span class="ce-courier-cost">£{{ c.cost }}</span>
                            </div>
                            <div v-if="form.courier_id === c.id" class="ce-courier-per-item">
                                <label class="ce-label ce-label--sm">Per item?</label>
                                <select v-model="form.courier_per_item" class="ce-input ce-input--sm">
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                        </label>
                        <label class="ce-check-item" :class="{ 'ce-check-item--active': form.courier_id === null }">
                            <input type="radio" name="courier" :checked="form.courier_id === null"
                                @change="handleCourierChange(0, false)" class="ce-checkbox" />
                            No courier
                        </label>
                    </div>
                </section>

                <!-- Fragrance formulation -->
                <section class="ce-card">
                    <h2 class="ce-card-title">
                        Fragrance Formulation
                        <span class="ce-card-title-note">for CLP</span>
                    </h2>

                    <!-- Total indicator -->
                    <div class="ce-total-bar" :class="materialsTotal > 100 ? 'ce-total-bar--over' : 'ce-total-bar--ok'">
                        <span>Total</span>
                        <strong>{{ materialsTotal.toFixed(2) }}%</strong>
                    </div>

                    <!-- Oil rows -->
                    <div class="ce-materials">
                        <div v-for="(mat, i) in form.materials" :key="i" class="ce-material-row">
                            <select v-model="mat.oil_id" class="ce-input ce-input--sm ce-material-select">
                                <option :value="0" disabled>Select oil…</option>
                                <option v-for="oil in availableOilsFor(i)" :key="oil.id" :value="oil.id">
                                    {{ oil.name }}{{ oil.supplier ? ` (${oil.supplier})` : '' }}
                                </option>
                                <option v-if="mat.oil_id !== 0 && !availableOilsFor(i).find(o => o.id === mat.oil_id)"
                                    :value="mat.oil_id">
                                    {{oils.find(o => o.id === mat.oil_id)?.name}}
                                </option>
                            </select>
                            <div class="ce-pct-wrap">
                                <input type="number" v-model="mat.percentage" min="0.01" max="100" step="0.01"
                                    placeholder="0.00" class="ce-input ce-input--sm ce-pct-input" />
                                <span class="ce-pct-symbol">%</span>
                            </div>
                            <button type="button" @click="removeMaterial(i)" class="ce-material-del"
                                aria-label="Remove oil">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.materials.length === 0" class="ce-empty-note">No oils linked yet.</p>
                        <p v-if="form.errors.materials" class="ce-err">{{ form.errors.materials }}</p>
                    </div>

                    <button type="button" @click="addMaterial" :disabled="form.materials.length >= oils.length"
                        class="ce-add-oil-btn">
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
/* ── Tokens ── */
.ce-header,
.ce-card {
    --bb-navy: #1a1a2e;
    --bb-cream: #faf9f7;
    --bb-surface: #ffffff;
    --bb-border: #ece8e2;
    --bb-text: #1a1a2e;
    --bb-muted: #7a7a9a;
    --bb-red: #e05c6e;
    --bb-red-bg: #fdeef0;
    --bb-green: #4caf7d;
    --bb-green-bg: #eef7f2;
    --bb-blush: #f2c4ce;
    --bb-lav: #c9b8f0;
    font-family: 'DM Sans', sans-serif;
}

/* ── Page header ── */
.ce-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.ce-breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--bb-muted);
    margin-bottom: 0.35rem;
}

.ce-breadcrumb-link {
    color: var(--bb-muted);
    text-decoration: none;
    transition: color 0.15s;
}

.ce-breadcrumb-link:hover {
    color: var(--bb-text);
}

.ce-breadcrumb-sep {
    opacity: 0.5;
}

.ce-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--bb-text);
}

.ce-sub {
    font-size: 0.82rem;
    color: var(--bb-muted);
    margin-top: 0.2rem;
}

.ce-unsaved {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    background: #fff8e6;
    border: 1px solid #e0c060;
    color: #8a6000;
    font-size: 0.78rem;
    font-weight: 600;
}

/* ── Layout ── */
.ce-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
    /* critical — stops columns stretching to equal height */
}

@media (max-width: 1024px) {
    .ce-grid {
        grid-template-columns: 1fr;
    }
}

.ce-left {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ce-right {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

@media (min-width: 1024px) {
    .ce-card--sticky {
        position: sticky;
        top: 80px;
    }
}

/* ── Cards ── */
.ce-card {
    background: var(--bb-surface);
    border-radius: 14px;
    border: 1px solid var(--bb-border);
    box-shadow: 0 1px 6px rgba(26, 26, 46, 0.05);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ce-card-title {
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--bb-muted);
    padding-bottom: 0.85rem;
    border-bottom: 1px solid var(--bb-border);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0;
}

.ce-card-title-note {
    font-size: 0.65rem;
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
    font-style: italic;
    color: var(--bb-muted);
    opacity: 0.75;
}

/* ── Fields ── */
.ce-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 640px) {
    .ce-field-row {
        grid-template-columns: 1fr;
    }
}

.ce-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.ce-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--bb-text);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.ce-label-note {
    font-size: 0.68rem;
    font-weight: 400;
    color: var(--bb-muted);
    font-style: italic;
}

.ce-input {
    width: 100%;
    padding: 0.62rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.ce-input:focus {
    border-color: #b8a9e8;
    box-shadow: 0 0 0 3px rgba(201, 184, 240, 0.2);
}

.ce-input--err {
    border-color: var(--bb-red);
}

.ce-input--sm {
    font-size: 0.82rem;
    padding: 0.48rem 0.7rem;
}

.ce-textarea {
    resize: vertical;
    min-height: 120px;
}

.ce-err {
    font-size: 0.75rem;
    color: var(--bb-red);
}

/* £ prefix */
.ce-input-prefix-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.ce-input-prefix {
    position: absolute;
    left: 0.85rem;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--bb-muted);
    pointer-events: none;
}

.ce-input--prefixed {
    padding-left: 1.8rem;
}

/* Slug */
.ce-slug-wrap {
    display: flex;
    align-items: stretch;
}

.ce-slug-prefix {
    padding: 0.62rem 0.75rem;
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
    border-right: none;
    border-radius: 8px 0 0 8px;
    font-size: 0.82rem;
    color: var(--bb-muted);
    white-space: nowrap;
}

.ce-slug-input {
    border-radius: 0 8px 8px 0;
}

/* ── Upload zone ── */
.ce-upload-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 1.5rem 1rem;
    border-radius: 10px;
    border: 2px dashed var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-muted);
    cursor: pointer;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 500;
    transition: border-color 0.15s, background 0.15s;
}

.ce-upload-zone:hover {
    border-color: #b8a9e8;
    background: #faf8ff;
}

.ce-upload-note {
    font-size: 0.72rem;
    font-weight: 400;
    opacity: 0.7;
}

.ce-upload-input {
    display: none;
}

/* ── Image queue ── */
.ce-queue {
    margin-top: 0;
}

.ce-queue-title {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--bb-muted);
    margin-bottom: 0.6rem;
}

.ce-queue-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.ce-queue-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: 8px;
    background: var(--bb-cream);
    border: 1px solid var(--bb-border);
}

.ce-queue-thumb {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: #e8e4f0;
    color: #9b84d4;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ce-queue-info {
    flex: 1;
    min-width: 0;
}

.ce-queue-name {
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--bb-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ce-queue-size {
    font-size: 0.7rem;
    color: var(--bb-muted);
}

.ce-queue-remove {
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
    transition: background 0.15s, color 0.15s;
}

.ce-queue-remove:hover {
    background: var(--bb-red-bg);
    color: var(--bb-red);
}

/* ── Existing images ── */
.ce-existing-imgs {}

.ce-img-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
    margin-top: 0.5rem;
}

@media (max-width: 640px) {
    .ce-img-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.ce-img-card {
    position: relative;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--bb-cream);
    transition: opacity 0.15s;
}

.ce-img-card--disabled {
    opacity: 0.5;
}

.ce-img-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.ce-img-disabled-label {
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

.ce-img-actions {
    position: absolute;
    bottom: 0;
    right: 0;
    display: flex;
    gap: 2px;
    padding: 3px;
    background: rgba(26, 26, 46, 0.55);
    border-top-left-radius: 6px;
}

.ce-img-btn {
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

.ce-img-btn:hover {
    opacity: 0.8;
}

.ce-img-btn--hide {
    background: #e0963a;
}

.ce-img-btn--show {
    background: var(--bb-green);
}

.ce-img-btn--del {
    background: var(--bb-red);
}

/* ── Status buttons ── */
.ce-status-btns {
    display: flex;
    gap: 0.5rem;
}

.ce-status-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.55rem 0.85rem;
    border-radius: 8px;
    border: 1px solid var(--bb-border);
    background: var(--bb-cream);
    color: var(--bb-muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}

.ce-status-btn--on {
    background: var(--bb-green-bg);
    border-color: var(--bb-green);
    color: #2a7a50;
}

.ce-status-btn--off {
    background: var(--bb-red-bg);
    border-color: var(--bb-red);
    color: var(--bb-red);
}

.ce-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

.ce-status-dot--green {
    background: var(--bb-green);
}

.ce-status-dot--grey {
    background: #b0b0c0;
}

/* ── Submit button ── */
.ce-submit-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.72rem 1.25rem;
    border-radius: 8px;
    border: none;
    background: var(--bb-navy);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.15s, transform 0.15s;
}

.ce-submit-btn:hover:not(:disabled) {
    opacity: 0.88;
    transform: translateY(-1px);
}

.ce-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.ce-unsaved-inline {
    text-align: center;
    font-size: 0.75rem;
    color: #8a6000;
    font-weight: 500;
}

/* ── Check list ── */
.ce-check-list {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    max-height: 220px;
    overflow-y: auto;
}

.ce-check-item {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.5rem 0.65rem;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--bb-text);
    cursor: pointer;
    transition: background 0.12s, border-color 0.12s;
}

.ce-check-item:hover {
    background: var(--bb-cream);
}

.ce-check-item--active {
    background: #faf8ff;
    border-color: #c9b8f0;
}

.ce-check-item--courier {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.4rem;
}

.ce-checkbox {
    accent-color: #9b84d4;
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    margin-top: 1px;
}

.ce-courier-info {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.ce-courier-name {
    font-size: 0.85rem;
    font-weight: 500;
}

.ce-courier-cost {
    font-size: 0.82rem;
    color: var(--bb-muted);
    font-weight: 600;
}

.ce-courier-per-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-left: 1.75rem;
}

/* ── Fragrance ── */
.ce-total-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.85rem;
    border-radius: 8px;
    font-size: 0.82rem;
}

.ce-total-bar--ok {
    background: var(--bb-green-bg);
    color: #2a7a50;
    border: 1px solid #b8dfc8;
}

.ce-total-bar--over {
    background: var(--bb-red-bg);
    color: var(--bb-red);
    border: 1px solid #f5b8c0;
}

.ce-materials {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ce-material-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ce-material-select {
    flex: 1;
}

.ce-pct-wrap {
    position: relative;
    width: 80px;
    flex-shrink: 0;
}

.ce-pct-input {
    padding-right: 1.5rem;
    width: 100%;
}

.ce-pct-symbol {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.8rem;
    color: var(--bb-muted);
    pointer-events: none;
}

.ce-material-del {
    width: 28px;
    height: 28px;
    border-radius: 6px;
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

.ce-material-del:hover {
    background: var(--bb-red-bg);
    color: var(--bb-red);
    border-color: var(--bb-red);
}

.ce-empty-note {
    font-size: 0.8rem;
    color: var(--bb-muted);
    font-style: italic;
}

.ce-add-oil-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    width: 100%;
    padding: 0.55rem;
    border-radius: 8px;
    border: 1.5px dashed var(--bb-border);
    background: none;
    color: var(--bb-muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.15s, color 0.15s, background 0.15s;
}

.ce-add-oil-btn:hover:not(:disabled) {
    border-color: #b8a9e8;
    color: #9b84d4;
    background: #faf8ff;
}

.ce-add-oil-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── Spinner ── */
.ce-spinner {
    width: 15px;
    height: 15px;
    animation: ce-spin 0.8s linear infinite;
}

@keyframes ce-spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
