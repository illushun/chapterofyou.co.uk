<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { debounce } from 'lodash';

// Interfaces for data structure
interface Category {
    id: number;
    name: string;
}

interface ParentProduct {
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
    // NEW: Added parent_product_id to the product interface
    parent_product_id: number | null;
    seo: {
        meta_title: string;
        meta_description: string;
        slug: string;
    };
}

// Define props passed from AdminProductController
const props = defineProps<{
    product?: Product; // Only present when editing
    categories: Category[];
    // NEW: Prop for the list of potential parent products
    parentProducts: ParentProduct[];
    selectedCategoryIds: number[]; // Array of category IDs for the product
    isEditing: boolean;
    errors: Record<string, string>;
}>();

// Initialize the form state
const form = useForm({
    mpn: props.product?.mpn || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    status: props.product?.status || 'enabled',
    cost: props.product?.cost.toString() || '0.00', // Use string for input type="number" consistency
    stock_qty: props.product?.stock_qty || 0,
    category_ids: props.selectedCategoryIds || ([] as number[]),
    parent_product_id: props.product?.parent_product_id || null,
    meta_title: props.product?.seo?.meta_title || '',
    meta_description: props.product?.seo?.meta_description || '',
    slug: props.product?.seo?.slug || '',
    // images: null, // Placeholder for file upload handling
});

// Computed properties
const title = computed(() => (props.isEditing ? `Edit Product: ${props.product?.name}` : 'Create New Product'));
const submitLabel = computed(() => (props.isEditing ? 'Update Product' : 'Create Product'));

// Debounced watch for auto-generating slug and meta title from name
watch(() => form.name, debounce((newName) => {
    if (!props.isEditing || !props.product?.seo?.slug) {
        // Only auto-generate slug/title if creating or if the existing slug hasn't been modified
        if (!form.slug || form.slug === props.product?.seo?.slug) {
            form.slug = newName.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        }
        if (!form.meta_title || form.meta_title === props.product?.seo?.meta_title) {
             form.meta_title = newName;
        }
    }
}, 500));

// Form submission logic
const submit = () => {
    // Convert string cost back to number for submission (though Laravel validation handles it)
    const dataToSubmit = {
        ...form.data(),
        cost: parseFloat(form.cost),
    };

    if (props.isEditing && props.product) {
        // PUT request for updating
        form.put(route('admin.products.update', props.product.id), {
            preserveScroll: true,
        });
    } else {
        // POST request for creation
        form.post(route('admin.products.store'), {
            preserveScroll: true,
        });
    }
};

const handleCategoryChange = (categoryId: number, isChecked: boolean) => {
    if (isChecked) {
        if (!form.category_ids.includes(categoryId)) {
            form.category_ids.push(categoryId);
        }
    } else {
        form.category_ids = form.category_ids.filter(id => id !== categoryId);
    }
};
</script>

<template>
    <AdminLayout>
        <Head :title="title" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">{{ title }}</h2>
            <p class="text-copy-light mt-1">{{ isEditing ? 'Modify product details, inventory, and SEO settings.' : 'Enter details for a new product.' }}</p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">General Information</h3>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-copy mb-1">Product Name</label>
                            <input type="text" id="name" v-model="form.name" required class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.name}" />
                            <div v-if="form.errors.name" class="text-xs text-error mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="mpn" class="block text-sm font-medium text-copy mb-1">MPN / SKU</label>
                            <input type="text" id="mpn" v-model="form.mpn" required class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.mpn}" />
                            <div v-if="form.errors.mpn" class="text-xs text-error mt-1">{{ form.errors.mpn }}</div>
                        </div>

                        <!-- NEW: Parent Product Selection Field -->
                        <div class="mb-4">
                            <label for="parent_product_id" class="block text-sm font-medium text-copy mb-1">Parent Product (for Variations)</label>
                            <select
                                id="parent_product_id"
                                v-model="form.parent_product_id"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                :class="{'border-error': form.errors.parent_product_id}"
                            >
                                <option :value="null">-- No Parent (Top-level Product) --</option>
                                <option
                                    v-for="parent in parentProducts"
                                    :key="parent.id"
                                    :value="parent.id"
                                    :disabled="isEditing && product && parent.id === product.id"
                                >
                                    {{ parent.name }}
                                    <template v-if="isEditing && product && parent.id === product.id"> (Cannot be self)</template>
                                </option>
                            </select>
                            <div v-if="form.errors.parent_product_id" class="text-xs text-error mt-1">{{ form.errors.parent_product_id }}</div>
                            <p class="text-xs text-copy-light mt-1">If this product is a variation (e.g., size, color), select its main parent product here. Leave as "No Parent" for top-level products.</p>
                        </div>
                        <!-- END NEW FIELD -->

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-copy mb-1">Description</label>
                            <textarea id="description" v-model="form.description" required rows="6" class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.description}"></textarea>
                            <div v-if="form.errors.description" class="text-xs text-error mt-1">{{ form.errors.description }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                         <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Pricing & Inventory</h3>

                         <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="cost" class="block text-sm font-medium text-copy mb-1">Unit Cost (excl. VAT)</label>
                                <input type="number" id="cost" v-model="form.cost" required min="0.01" step="0.01" class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.cost}" />
                                <div v-if="form.errors.cost" class="text-xs text-error mt-1">{{ form.errors.cost }}</div>
                            </div>

                            <div class="mb-4">
                                <label for="stock_qty" class="block text-sm font-medium text-copy mb-1">Stock Quantity</label>
                                <input type="number" id="stock_qty" v-model="form.stock_qty" required min="0" step="1" class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.stock_qty}" />
                                <div v-if="form.errors.stock_qty" class="text-xs text-error mt-1">{{ form.errors.stock_qty }}</div>
                            </div>
                         </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">SEO & URL</h3>

                        <div class="mb-4">
                            <label for="meta_title" class="block text-sm font-medium text-copy mb-1">Meta Title</label>
                            <input type="text" id="meta_title" v-model="form.meta_title" maxlength="255" class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.meta_title}" />
                            <div v-if="form.errors.meta_title" class="text-xs text-error mt-1">{{ form.errors.meta_title }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="meta_description" class="block text-sm font-medium text-copy mb-1">Meta Description (Max 500 chars)</label>
                            <textarea id="meta_description" v-model="form.meta_description" rows="3" maxlength="500" class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'border-error': form.errors.meta_description}"></textarea>
                            <div v-if="form.errors.meta_description" class="text-xs text-error mt-1">{{ form.errors.meta_description }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-copy mb-1">URL Slug</label>
                            <div class="flex items-center">
                                <span class="bg-secondary-light border-y-2 border-l-2 border-copy p-3 rounded-l-lg text-copy-light text-sm hidden sm:block">/product/</span>
                                <input type="text" id="slug" v-model="form.slug" required class="flex-grow rounded-r-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm" :class="{'rounded-l-lg': !isEditing, 'border-error': form.errors.slug}" />
                            </div>
                            <div v-if="form.errors.slug" class="text-xs text-error mt-1">{{ form.errors.slug }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Status & Actions</h3>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-copy mb-1">Product Status</label>
                            <select id="status" v-model="form.status" required class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm">
                                <option value="enabled">Enabled (Live)</option>
                                <option value="disabled">Disabled (Draft/Hidden)</option>
                            </select>
                            <div v-if="form.errors.status" class="text-xs text-error mt-1">{{ form.errors.status }}</div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="mt-4 w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background-color: var(--primary); color: var(--primary-content);"
                        >
                            {{ form.processing ? 'Saving...' : submitLabel }}
                        </button>

                        <div v-if="form.isDirty" class="mt-2 text-center text-sm text-yellow-600 font-medium">Unsaved changes</div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Categories</h3>

                        <div class="max-h-64 overflow-y-auto pr-2">
                            <ul class="space-y-3">
                                <li v-for="category in categories" :key="category.id">
                                    <label :for="'category-' + category.id" class="inline-flex items-center gap-3 cursor-pointer transition hover:text-primary-content">
                                        <input
                                            type="checkbox"
                                            :id="'category-' + category.id"
                                            :value="category.id"
                                            :checked="form.category_ids.includes(category.id)"
                                            @change="handleCategoryChange(category.id, ($event.target as HTMLInputElement).checked)"
                                            class="size-5 border-2 border-copy text-primary focus:ring-primary"
                                        />
                                        <span class="text-sm text-copy font-medium"> {{ category.name }} </span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <div v-if="form.errors.category_ids" class="text-xs text-error mt-2">{{ form.errors.category_ids }}</div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Product Images</h3>
                        <div class="h-24 border-2 border-dashed border-copy-light flex items-center justify-center text-copy-light p-4">
                            Image Upload Area Placeholder
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
