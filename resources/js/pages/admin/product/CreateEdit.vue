<script setup lang="ts">
import ProductFaqEditor from '@/components/admin/product/ProductFaqEditor.vue';
import ProductImagesEditor from '@/components/admin/product/ProductImagesEditor.vue';
import ProductUsageEditor from '@/components/admin/product/ProductUsageEditor.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { MOOD_TAGS, ROOMS, SCENT_FAMILIES } from '@/lib/scentTaxonomy';
import type { EditableProductImage, ProductFaq } from '@/types/product';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useDebounceFn as debounce } from '@vueuse/core';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import '../../../../css/admin-product-fields.css';

interface Category {
    id: number;
    name: string;
}
interface Courier {
    id: number;
    name: string;
    type: string;
    status: string;
    cost: number;
}
interface ParentProduct {
    id: number;
    name: string;
}
interface RefillProduct {
    id: number;
    name: string;
}
interface Product {
    id: number;
    mpn: string;
    name: string;
    description: string;
    status: 'enabled' | 'disabled';
    cost: number;
    stock_qty: number;
    details: string;
    parent_product_id: number | null;
    how_to_use: string | null;
    seo: { meta_title: string; meta_description: string; slug: string };
}
interface ScentTagSuggestion {
    scent_families: string[];
    mood_tags: string[];
    room_tags: string[];
    reasoning: string;
}
interface Oil {
    id: number;
    name: string;
    supplier: string | null;
    cas_primary: string | null;
}
interface ProductMaterial {
    oil_id: number;
    percentage: string;
}

const props = defineProps<{
    product?: Product;
    categories: Category[];
    couriers: Courier[];
    parentProducts: ParentProduct[];
    refillCandidates: RefillProduct[];
    selectedCategoryIds: number[];
    selectedRefillProductIds?: number[];
    selectedCourierId: number | null;
    courierPerItem: string;
    oils: Oil[];
    productMaterials: ProductMaterial[];
    productFaqs: ProductFaq[];
    productImages: EditableProductImage[];
    isEditing: boolean;
    errors: Record<string, string>;
    selectedScentFamilies?: string[];
    selectedMoodTags?: string[];
    selectedRoomTags?: string[];
}>();

const form = useForm({
    mpn: props.product?.mpn || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    details: props.product?.details || '',
    how_to_use: props.product?.how_to_use || '',
    faqs: (props.productFaqs ?? []) as ProductFaq[],
    status: props.product?.status || 'enabled',
    cost: props.product?.cost?.toString() || '0.00',
    stock_qty: props.product?.stock_qty || 0,
    parent_product_id: props.product?.parent_product_id || null,
    refill_product_ids: (props.selectedRefillProductIds ?? []) as number[],
    category_ids: props.selectedCategoryIds || ([] as number[]),
    courier_id: props.selectedCourierId || null,
    courier_per_item: props.courierPerItem || 'no',
    materials: (props.productMaterials ?? []).map((m) => ({
        oil_id: m.oil_id,
        percentage: m.percentage,
    })) as ProductMaterial[],
    scent_families: (props.selectedScentFamilies ?? []) as string[],
    mood_tags: (props.selectedMoodTags ?? []) as string[],
    room_tags: (props.selectedRoomTags ?? []) as string[],
    meta_title: props.product?.seo?.meta_title || '',
    meta_description: props.product?.seo?.meta_description || '',
    slug: props.product?.seo?.slug || '',
    new_images: [] as File[],
    images_to_delete: [] as number[],
    images_to_toggle: [] as number[],
});

const title = computed(() =>
    props.isEditing ? `Edit: ${props.product?.name}` : 'New Product',
);
const submitLabel = computed(() =>
    props.isEditing ? 'Save Changes' : 'Create Product',
);

watch(
    () => form.name,
    debounce((n: string) => {
        if (form.processing) return;
        const slug = n
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-]/g, '');
        if (!form.slug || form.slug === props.product?.seo?.slug)
            form.slug = slug;
        if (
            !form.meta_title ||
            form.meta_title === props.product?.seo?.meta_title
        )
            form.meta_title = n;
    }, 500),
);

// ── Category / courier ─────────────────────────────────────────────────────
const handleCategoryChange = (id: number, checked: boolean) => {
    if (checked) {
        if (!form.category_ids.includes(id)) {
            form.category_ids.push(id);
        }
    } else {
        form.category_ids = form.category_ids.filter((c) => c !== id);
    }
};
const handleCourierChange = (id: number, checked: boolean) => {
    form.courier_id = checked ? id : null;
    form.courier_per_item = 'no';
};
const handleRefillChange = (id: number, checked: boolean) => {
    if (checked) {
        if (!form.refill_product_ids.includes(id)) {
            form.refill_product_ids.push(id);
        }
    } else {
        form.refill_product_ids = form.refill_product_ids.filter(
            (r) => r !== id,
        );
    }
};

// ── Oil formulation ────────────────────────────────────────────────────────
const addMaterial = () => form.materials.push({ oil_id: 0, percentage: '' });
const removeMaterial = (i: number) => form.materials.splice(i, 1);
const availableOilsFor = (idx: number) => {
    const taken = form.materials
        .map((m, i) => (i !== idx ? m.oil_id : null))
        .filter((id) => id !== null && id !== 0) as number[];
    return props.oils.filter((o) => !taken.includes(o.id));
};
const materialsTotal = computed(() =>
    form.materials.reduce((s, m) => s + (parseFloat(m.percentage) || 0), 0),
);

// ── Scent profile ──────────────────────────────────────────────────────────
const toggleScentFamily = (value: string, checked: boolean) => {
    if (checked) {
        if (!form.scent_families.includes(value)) {
            form.scent_families.push(value);
        }
    } else {
        form.scent_families = form.scent_families.filter((v) => v !== value);
    }
};
const toggleMoodTag = (value: string, checked: boolean) => {
    if (checked) {
        if (!form.mood_tags.includes(value)) {
            form.mood_tags.push(value);
        }
    } else {
        form.mood_tags = form.mood_tags.filter((v) => v !== value);
    }
};
const toggleRoomTag = (value: string, checked: boolean) => {
    if (checked) {
        if (!form.room_tags.includes(value)) {
            form.room_tags.push(value);
        }
    } else {
        form.room_tags = form.room_tags.filter((v) => v !== value);
    }
};

const aiSuggesting = ref(false);
const aiReasoning = ref('');
const aiError = ref('');

const suggestScentTags = async () => {
    if (!form.name) return;
    aiSuggesting.value = true;
    aiError.value = '';
    aiReasoning.value = '';

    try {
        const { data } = await axios.post<ScentTagSuggestion>(
            route('admin.products.suggest-scent-tags'),
            {
                name: form.name,
                description: form.description,
                details: form.details,
            },
        );

        form.scent_families = data.scent_families;
        form.mood_tags = data.mood_tags;
        form.room_tags = data.room_tags;
        aiReasoning.value = data.reasoning;
    } catch (e: any) {
        aiError.value =
            e?.response?.data?.message ??
            'Suggestion failed. Please try again.';
    } finally {
        aiSuggesting.value = false;
    }
};

// ── Submit ─────────────────────────────────────────────────────────────────
const submit = () => {
    (form.data() as any).cost = parseFloat(form.cost);
    if (props.isEditing && props.product) {
        form.transform((d) => ({ ...d, _method: 'put' })).post(
            route('admin.products.update', props.product.id),
            {
                preserveScroll: true,
                onSuccess: () => {
                    form.new_images = [];
                    form.images_to_delete = [];
                    form.images_to_toggle = [];
                },
            },
        );
    } else {
        form.post(route('admin.products.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.new_images = [];
                form.images_to_delete = [];
                form.images_to_toggle = [];
            },
        });
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${title} : Admin`" />

        <!-- Header -->
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link
                        :href="route('admin.products.index')"
                        class="adm-breadcrumb a"
                        >Products</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="adm-title">{{ title }}</h1>
                <p class="adm-sub">
                    {{
                        isEditing
                            ? 'Update details, inventory, images and SEO.'
                            : 'Fill in the details for a new product.'
                    }}
                </p>
            </div>
            <div v-if="form.isDirty" class="adm-unsaved">
                <svg
                    width="13"
                    height="13"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
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
                            <label class="adm-label" for="name"
                                >Product Name</label
                            >
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                class="adm-input"
                                :class="{ 'adm-input--err': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="adm-err">
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label" for="mpn">MPN / SKU</label>
                            <input
                                id="mpn"
                                type="text"
                                v-model="form.mpn"
                                required
                                class="adm-input"
                                :class="{ 'adm-input--err': form.errors.mpn }"
                            />
                            <p v-if="form.errors.mpn" class="adm-err">
                                {{ form.errors.mpn }}
                            </p>
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label" for="parent">
                            Parent Product
                            <span class="adm-label-note">(for variations)</span>
                        </label>
                        <select
                            id="parent"
                            v-model="form.parent_product_id"
                            class="adm-select"
                        >
                            <option :value="null">
                                No parent (standalone product)
                            </option>
                            <option
                                v-for="p in parentProducts"
                                :key="p.id"
                                :value="p.id"
                                :disabled="isEditing && p.id === product?.id"
                            >
                                {{ p.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.parent_product_id" class="adm-err">
                            {{ form.errors.parent_product_id }}
                        </p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label">
                            Add-on products
                            <span class="adm-label-note"
                                >(optional, customers can select more than one
                                on the product page)</span
                            >
                        </label>
                        <div class="adm-check-list">
                            <label
                                v-for="p in refillCandidates"
                                :key="p.id"
                                class="adm-check-item"
                                :class="{
                                    'adm-check-item--active':
                                        form.refill_product_ids.includes(p.id),
                                }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="
                                        form.refill_product_ids.includes(p.id)
                                    "
                                    @change="
                                        handleRefillChange(
                                            p.id,
                                            ($event.target as HTMLInputElement)
                                                .checked,
                                        )
                                    "
                                    class="adm-checkbox"
                                />
                                {{ p.name }}
                            </label>
                        </div>
                        <p
                            v-if="form.errors.refill_product_ids"
                            class="adm-err"
                        >
                            {{ form.errors.refill_product_ids }}
                        </p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label" for="description"
                            >Description</label
                        >
                        <textarea
                            id="description"
                            v-model="form.description"
                            required
                            rows="7"
                            class="adm-textarea"
                            :class="{
                                'adm-input--err': form.errors.description,
                            }"
                        ></textarea>
                        <p v-if="form.errors.description" class="adm-err">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label" for="details">Details</label>
                        <textarea
                            id="details"
                            v-model="form.details"
                            required
                            rows="7"
                            class="adm-textarea"
                            :class="{ 'adm-input--err': form.errors.details }"
                        ></textarea>
                        <p v-if="form.errors.details" class="adm-err">
                            {{ form.errors.details }}
                        </p>
                    </div>
                </section>

                <!-- Pricing & inventory -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Pricing &amp; Inventory</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <label class="adm-label" for="cost"
                                >Unit Cost (excl. VAT)</label
                            >
                            <div class="adm-prefix-wrap">
                                <span class="adm-prefix">£</span>
                                <input
                                    id="cost"
                                    type="number"
                                    v-model="form.cost"
                                    required
                                    min="0.01"
                                    step="0.01"
                                    class="adm-input adm-input--prefixed"
                                    :class="{
                                        'adm-input--err': form.errors.cost,
                                    }"
                                />
                            </div>
                            <p v-if="form.errors.cost" class="adm-err">
                                {{ form.errors.cost }}
                            </p>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label" for="stock"
                                >Stock Quantity</label
                            >
                            <input
                                id="stock"
                                type="number"
                                v-model="form.stock_qty"
                                required
                                min="0"
                                step="1"
                                class="adm-input"
                                :class="{
                                    'adm-input--err': form.errors.stock_qty,
                                }"
                            />
                            <p v-if="form.errors.stock_qty" class="adm-err">
                                {{ form.errors.stock_qty }}
                            </p>
                        </div>
                    </div>
                </section>

                <ProductImagesEditor
                    :images="productImages"
                    :error="form.errors.new_images"
                    v-model:new-images="form.new_images"
                    v-model:deleted-ids="form.images_to_delete"
                    v-model:toggled-ids="form.images_to_toggle"
                />

                <ProductUsageEditor
                    v-model="form.how_to_use"
                    :error="form.errors.how_to_use"
                />

                <ProductFaqEditor v-model="form.faqs" />

                <!-- SEO -->
                <section class="adm-card">
                    <h2 class="adm-card-title">SEO &amp; URL</h2>
                    <div class="adm-field">
                        <label class="adm-label" for="meta_title"
                            >Meta Title</label
                        >
                        <input
                            id="meta_title"
                            type="text"
                            v-model="form.meta_title"
                            maxlength="255"
                            class="adm-input"
                            :class="{
                                'adm-input--err': form.errors.meta_title,
                            }"
                        />
                        <p v-if="form.errors.meta_title" class="adm-err">
                            {{ form.errors.meta_title }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="meta_desc">
                            Meta Description
                            <span class="adm-label-note">max 500 chars</span>
                        </label>
                        <textarea
                            id="meta_desc"
                            v-model="form.meta_description"
                            rows="3"
                            maxlength="500"
                            class="adm-textarea"
                            :class="{
                                'adm-input--err': form.errors.meta_description,
                            }"
                        ></textarea>
                        <p v-if="form.errors.meta_description" class="adm-err">
                            {{ form.errors.meta_description }}
                        </p>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label" for="slug">URL Slug</label>
                        <div class="adm-slug-wrap">
                            <span class="adm-slug-prefix">/product/</span>
                            <input
                                id="slug"
                                type="text"
                                v-model="form.slug"
                                required
                                class="adm-input adm-slug-input"
                                :class="{ 'adm-input--err': form.errors.slug }"
                            />
                        </div>
                        <p v-if="form.errors.slug" class="adm-err">
                            {{ form.errors.slug }}
                        </p>
                    </div>
                </section>
            </div>

            <!-- ── Right column ── -->
            <div class="adm-form-right">
                <!-- Status & submit, sticky on desktop only -->
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Status &amp; Actions</h2>
                    <div class="adm-field">
                        <label class="adm-label">Product Status</label>
                        <div class="adm-status-btns">
                            <button
                                type="button"
                                @click="form.status = 'enabled'"
                                class="adm-status-btn"
                                :class="{
                                    'adm-status-btn--on':
                                        form.status === 'enabled',
                                }"
                            >
                                <span
                                    class="adm-status-dot adm-status-dot--green"
                                ></span>
                                Active
                            </button>
                            <button
                                type="button"
                                @click="form.status = 'disabled'"
                                class="adm-status-btn"
                                :class="{
                                    'adm-status-btn--off':
                                        form.status === 'disabled',
                                }"
                            >
                                <span
                                    class="adm-status-dot adm-status-dot--grey"
                                ></span>
                                Inactive
                            </button>
                        </div>
                        <p v-if="form.errors.status" class="adm-err">
                            {{ form.errors.status }}
                        </p>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="adm-submit"
                    >
                        <svg
                            v-if="form.processing"
                            class="adm-spinner"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="rgba(255,255,255,0.3)"
                                stroke-width="3"
                            />
                            <path
                                d="M12 2a10 10 0 0 1 10 10"
                                stroke="var(--adm-paper-raised)"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                        </svg>
                        {{ form.processing ? 'Saving...' : submitLabel }}
                    </button>
                    <p v-if="form.isDirty" class="adm-unsaved-inline">
                        Unsaved changes
                    </p>
                </section>

                <!-- Categories -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Categories</h2>
                    <div class="adm-check-list">
                        <label
                            v-for="cat in categories"
                            :key="cat.id"
                            class="adm-check-item"
                            :class="{
                                'adm-check-item--active':
                                    form.category_ids.includes(cat.id),
                            }"
                        >
                            <input
                                type="checkbox"
                                :checked="form.category_ids.includes(cat.id)"
                                @change="
                                    handleCategoryChange(
                                        cat.id,
                                        ($event.target as HTMLInputElement)
                                            .checked,
                                    )
                                "
                                class="adm-checkbox"
                            />
                            {{ cat.name }}
                        </label>
                    </div>
                    <p v-if="form.errors.category_ids" class="adm-err">
                        {{ form.errors.category_ids }}
                    </p>
                </section>

                <!-- Scent Profile -->
                <section class="adm-card">
                    <h2 class="adm-card-title">
                        Scent Profile
                        <span class="adm-card-title-note"
                            >powers the Scent Finder quiz</span
                        >
                    </h2>

                    <button
                        type="button"
                        @click="suggestScentTags"
                        :disabled="aiSuggesting || !form.name"
                        class="pe-dashed-btn"
                        style="margin-bottom: 0.85rem"
                    >
                        <svg
                            v-if="!aiSuggesting"
                            width="13"
                            height="13"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M12 3v3M12 18v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M3 12h3M18 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"
                            />
                        </svg>
                        <svg
                            v-else
                            class="adm-spinner"
                            width="13"
                            height="13"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="var(--adm-line)"
                                stroke-width="3"
                            />
                            <path
                                d="M12 2a10 10 0 0 1 10 10"
                                stroke="var(--adm-stamp-deep)"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                        </svg>
                        {{ aiSuggesting ? 'Suggesting…' : 'Suggest with AI' }}
                    </button>
                    <p v-if="aiError" class="adm-err">{{ aiError }}</p>
                    <p
                        v-if="aiReasoning"
                        class="pe-hint"
                        style="margin-bottom: 0.85rem"
                    >
                        {{ aiReasoning }}
                    </p>

                    <p class="adm-label" style="margin-bottom: 0.4rem">
                        Scent Families
                    </p>
                    <div class="adm-check-list">
                        <label
                            v-for="family in SCENT_FAMILIES"
                            :key="family.value"
                            class="adm-check-item"
                            :class="{
                                'adm-check-item--active':
                                    form.scent_families.includes(family.value),
                            }"
                        >
                            <input
                                type="checkbox"
                                :checked="
                                    form.scent_families.includes(family.value)
                                "
                                @change="
                                    toggleScentFamily(
                                        family.value,
                                        ($event.target as HTMLInputElement)
                                            .checked,
                                    )
                                "
                                class="adm-checkbox"
                            />
                            {{ family.label }}
                        </label>
                    </div>
                    <p v-if="form.errors.scent_families" class="adm-err">
                        {{ form.errors.scent_families }}
                    </p>

                    <p class="adm-label" style="margin: 0.85rem 0 0.4rem">
                        Mood / Occasion
                    </p>
                    <div class="adm-check-list">
                        <label
                            v-for="mood in MOOD_TAGS"
                            :key="mood.value"
                            class="adm-check-item"
                            :class="{
                                'adm-check-item--active':
                                    form.mood_tags.includes(mood.value),
                            }"
                        >
                            <input
                                type="checkbox"
                                :checked="form.mood_tags.includes(mood.value)"
                                @change="
                                    toggleMoodTag(
                                        mood.value,
                                        ($event.target as HTMLInputElement)
                                            .checked,
                                    )
                                "
                                class="adm-checkbox"
                            />
                            {{ mood.label }}
                        </label>
                    </div>
                    <p v-if="form.errors.mood_tags" class="adm-err">
                        {{ form.errors.mood_tags }}
                    </p>

                    <p class="adm-label" style="margin: 0.85rem 0 0.4rem">
                        Best Suited Rooms
                    </p>
                    <div class="adm-check-list">
                        <label
                            v-for="room in ROOMS"
                            :key="room.value"
                            class="adm-check-item"
                            :class="{
                                'adm-check-item--active':
                                    form.room_tags.includes(room.value),
                            }"
                        >
                            <input
                                type="checkbox"
                                :checked="form.room_tags.includes(room.value)"
                                @change="
                                    toggleRoomTag(
                                        room.value,
                                        ($event.target as HTMLInputElement)
                                            .checked,
                                    )
                                "
                                class="adm-checkbox"
                            />
                            {{ room.label }}
                        </label>
                    </div>
                    <p v-if="form.errors.room_tags" class="adm-err">
                        {{ form.errors.room_tags }}
                    </p>
                </section>

                <!-- Couriers -->
                <section class="adm-card">
                    <h2 class="adm-card-title">Courier</h2>
                    <div class="adm-check-list">
                        <label
                            v-for="c in couriers"
                            :key="c.id"
                            class="adm-check-item pe-courier-item"
                            :class="{
                                'adm-check-item--active':
                                    form.courier_id === c.id,
                            }"
                        >
                            <div class="pe-courier-row">
                                <input
                                    type="radio"
                                    name="courier"
                                    :value="c.id"
                                    :checked="form.courier_id === c.id"
                                    @change="handleCourierChange(c.id, true)"
                                    class="adm-checkbox"
                                />
                                <span class="pe-courier-name"
                                    >{{ c.type }}: {{ c.name }}</span
                                >
                                <span class="pe-courier-cost"
                                    >£{{ c.cost }}</span
                                >
                            </div>
                            <div
                                v-if="form.courier_id === c.id"
                                class="pe-courier-per-item"
                            >
                                <span
                                    class="adm-label"
                                    style="font-size: 0.7rem"
                                    >Per item?</span
                                >
                                <select
                                    v-model="form.courier_per_item"
                                    class="adm-select adm-input--sm"
                                >
                                    <option value="no">No</option>
                                    <option value="yes">Yes</option>
                                </select>
                            </div>
                        </label>
                        <label
                            class="adm-check-item"
                            :class="{
                                'adm-check-item--active':
                                    form.courier_id === null,
                            }"
                        >
                            <input
                                type="radio"
                                name="courier"
                                :checked="form.courier_id === null"
                                @change="handleCourierChange(0, false)"
                                class="adm-checkbox"
                            />
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
                    <div
                        class="pe-total-bar"
                        :class="
                            materialsTotal > 100
                                ? 'pe-total-bar--over'
                                : 'pe-total-bar--ok'
                        "
                    >
                        <span>Total</span>
                        <strong>{{ materialsTotal.toFixed(2) }}%</strong>
                    </div>
                    <div class="pe-materials">
                        <div
                            v-for="(mat, i) in form.materials"
                            :key="i"
                            class="pe-material-row"
                        >
                            <select
                                v-model="mat.oil_id"
                                class="adm-select adm-input--sm pe-material-select"
                            >
                                <option :value="0" disabled>Select oil…</option>
                                <option
                                    v-for="oil in availableOilsFor(i)"
                                    :key="oil.id"
                                    :value="oil.id"
                                >
                                    {{ oil.name
                                    }}{{
                                        oil.supplier ? ` (${oil.supplier})` : ''
                                    }}
                                </option>
                                <option
                                    v-if="
                                        mat.oil_id !== 0 &&
                                        !availableOilsFor(i).find(
                                            (o) => o.id === mat.oil_id,
                                        )
                                    "
                                    :value="mat.oil_id"
                                >
                                    {{
                                        oils.find((o) => o.id === mat.oil_id)
                                            ?.name
                                    }}
                                </option>
                            </select>
                            <div class="pe-pct-wrap">
                                <input
                                    type="number"
                                    v-model="mat.percentage"
                                    min="0.01"
                                    max="100"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="adm-input adm-input--sm pe-pct-input"
                                />
                                <span class="pe-pct-symbol">%</span>
                            </div>
                            <button
                                type="button"
                                @click="removeMaterial(i)"
                                class="pe-icon-del"
                                aria-label="Remove"
                            >
                                <svg
                                    width="12"
                                    height="12"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M18 6 6 18M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p
                            v-if="form.materials.length === 0"
                            class="pe-empty-note"
                        >
                            No oils linked yet.
                        </p>
                        <p v-if="form.errors.materials" class="adm-err">
                            {{ form.errors.materials }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="addMaterial"
                        :disabled="form.materials.length >= oils.length"
                        class="pe-dashed-btn"
                    >
                        <svg
                            width="13"
                            height="13"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        >
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
    color: var(--adm-ink-dim);
    font-weight: 600;
}

.pe-courier-per-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-left: 1.6rem;
}
.pe-total-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.85rem;
    border-radius: var(--adm-radius);
    font-size: 0.82rem;
}

.pe-total-bar--ok {
    background: var(--adm-success-bg);
    color: var(--adm-success);
    border: 1px solid var(--adm-success-line);
}

.pe-total-bar--over {
    background: var(--adm-danger-bg);
    color: var(--adm-danger);
    border: 1px solid var(--adm-danger-line);
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
    color: var(--adm-ink-dim);
    pointer-events: none;
}

.pe-icon-del {
    width: 28px;
    height: 28px;
    border-radius: var(--adm-radius-sm);
    border: 1px solid var(--adm-line);
    background: none;
    color: var(--adm-ink-dim);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition:
        background 0.12s,
        color 0.12s,
        border-color 0.12s;
}

.pe-icon-del:hover {
    background: var(--adm-danger-bg);
    color: var(--adm-danger);
    border-color: var(--adm-danger);
}
</style>
