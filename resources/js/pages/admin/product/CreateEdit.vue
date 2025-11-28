<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { debounce } from 'lodash';

// Interfaces for data structure
interface Category {
    id: number;
    name: string;
}

interface Courier {
    id: number;
    name: string;
    type: 'Royal Mail' | 'FedEx' | 'Evri' | 'DPD';
    status: 'enabled' | 'disabled';
    cost: number;
}

interface ParentProduct {
    id: number;
    name: string;
}

interface ProductImage {
    id: number;
    product_id: number;
    image: string; // The storage path
    status: 'enabled' | 'disabled';
    file_path: string; // The URL for the frontend
    is_enabled: boolean; // Computed boolean status for FE ease
}

interface Product {
    id: number;
    mpn: string;
    name: string;
    description: string;
    status: 'enabled' | 'disabled';
    cost: number;
    stock_qty: number;
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
    couriers: Courier[];
    parentProducts: ParentProduct[];
    selectedCategoryIds: number[]; // Array of category IDs for the product
    selectedCourierId: number | null; // courier ID for the product
    courierPerItem: string; // Whether courier is charged per item
    productImages: ProductImage[]; // Array of existing images for the product
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
    parent_product_id: props.product?.parent_product_id || null, // New field
    category_ids: props.selectedCategoryIds || ([] as number[]),
    courier_id: props.selectedCourierId || null,
    courier_per_item: props.courierPerItem || "no",
    meta_title: props.product?.seo?.meta_title || '',
    meta_description: props.product?.seo?.meta_description || '',
    slug: props.product?.seo?.slug || '',

    // Image Handling State
    new_images: [] as File[], // Files queued for upload
    images_to_delete: [] as number[], // IDs of existing images to delete
    images_to_toggle: [] as number[], // IDs of existing images whose status should be flipped
});

// Computed properties
const title = computed(() => (props.isEditing ? `Edit Product: ${props.product?.name}` : 'Create New Product'));
const submitLabel = computed(() => (props.isEditing ? 'Update Product' : 'Create Product'));

// Filter existing images based on deletion queue
const filteredExistingImages = computed(() => {
    return props.productImages.filter(image =>
        !form.images_to_delete.includes(image.id)
    ).map(image => {
        // Apply pending status toggle for display purposes
        if (form.images_to_toggle.includes(image.id)) {
            return { ...image, is_enabled: !image.is_enabled };
        }
        return image;
    });
});

// Debounced watch for auto-generating slug and meta title from name
watch(() => form.name, debounce((newName) => {
    // Only proceed if not processing a submission
    if (form.processing) return;

    if (!props.isEditing || !props.product?.seo?.slug || form.name !== props.product?.name) {
        const slugBase = newName.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');

        // Only auto-generate slug/title if creating or if the existing slug/title is untouched
        if (!form.slug || form.slug === props.product?.seo?.slug) {
            form.slug = slugBase;
        }
        if (!form.meta_title || form.meta_title === props.product?.seo?.meta_title) {
            form.meta_title = newName;
        }
    }
}, 500));

// --- Image Management Methods ---

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        // Convert FileList to array and append to new_images
        Array.from(target.files).forEach(file => {
            if (form.new_images.length < 5) {
                form.new_images.push(file);
            }
        });
        // Reset file input to allow uploading the same file again if needed
        target.value = '';
    }
};

const removeNewImage = (index: number) => {
    form.new_images.splice(index, 1);
};

const toggleImageStatus = (imageId: number) => {
    const index = form.images_to_toggle.indexOf(imageId);
    if (index === -1) {
        // Add to toggle list
        form.images_to_toggle.push(imageId);
    } else {
        // Remove from toggle list
        form.images_to_toggle.splice(index, 1);
    }
};

const deleteExistingImage = (imageId: number) => {
    if (!form.images_to_delete.includes(imageId)) {
        form.images_to_delete.push(imageId);
    }

    // If an image is pending deletion, it shouldn't be pending toggle
    const toggleIndex = form.images_to_toggle.indexOf(imageId);
    if (toggleIndex !== -1) {
        form.images_to_toggle.splice(toggleIndex, 1);
    }
};

// --- Category Management Method ---
const handleCategoryChange = (categoryId: number, isChecked: boolean) => {
    if (isChecked) {
        if (!form.category_ids.includes(categoryId)) {
            form.category_ids.push(categoryId);
        }
    } else {
        form.category_ids = form.category_ids.filter(id => id !== categoryId);
    }
};

const handleCourierChange = (courierId: number, isChecked: boolean) => {
    if (isChecked) {
        form.courier_id = courierId;
        form.courier_per_item = "no";
    } else {
        form.courier_id = null;
        form.courier_per_item = "no";
    }
};

// --- Form submission logic ---
const submit = () => {
    // Convert string cost back to number for submission (though Laravel validation handles it)
    form.data().cost = parseFloat(form.cost);

    if (props.isEditing && props.product) {
        // When updating an existing product, files must be sent via POST,
        // and we use transform to inject the _method: 'put' field for method spoofing.
        form.transform((data) => ({
            ...data,
            // Explicitly set _method to 'put' for Laravel to handle multipart/form-data POST as a PUT request
            _method: 'put',
        }))
            // Use form.post() when uploading files, even for PUT requests
            .post(route('admin.products.update', props.product.id), {
                preserveScroll: true,
                // Reset image arrays after successful submission
                onSuccess: () => {
                    form.new_images = [];
                    form.images_to_delete = [];
                    form.images_to_toggle = [];
                }
            });
    } else {
        // POST request for creation (no method spoofing needed)
        form.post(route('admin.products.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.new_images = [];
                form.images_to_delete = [];
                form.images_to_toggle = [];
            }
        });
    }
};

const formatImageSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

</script>

<template>
    <AdminLayout>

        <Head :title="title" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">{{ title }}</h2>
            <p class="text-copy-light mt-1">{{ isEditing ? 'Modify product details, inventory, and SEO settings.' :
                'Enter details for a new product.' }}</p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">General
                            Information</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-copy mb-1">Product Name</label>
                                <input type="text" id="name" v-model="form.name" required
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.name }" />
                                <div v-if="form.errors.name" class="text-xs text-error mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="mpn" class="block text-sm font-medium text-copy mb-1">MPN / SKU</label>
                                <input type="text" id="mpn" v-model="form.mpn" required
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.mpn }" />
                                <div v-if="form.errors.mpn" class="text-xs text-error mt-1">{{ form.errors.mpn }}</div>
                            </div>
                        </div>

                        <!-- Parent Product ID Field -->
                        <div class="mb-4">
                            <label for="parent_product_id" class="block text-sm font-medium text-copy mb-1">Parent
                                Product (for variations)</label>
                            <select id="parent_product_id" v-model="form.parent_product_id"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                :class="{ 'border-error': form.errors.parent_product_id }">
                                <option :value="null">-- No Parent (This is a main product) --</option>
                                <option v-for="p in parentProducts" :key="p.id" :value="p.id"
                                    :disabled="isEditing && p.id === product?.id">
                                    {{ p.name }} (ID: {{ p.id }})
                                </option>
                            </select>
                            <div v-if="form.errors.parent_product_id" class="text-xs text-error mt-1">{{
                                form.errors.parent_product_id }}</div>
                            <p v-if="isEditing && product?.parent_product_id === product?.id"
                                class="text-xs text-error mt-1">This product is currently set as its own parent, which
                                is invalid. Please select a different parent or 'No Parent'.</p>
                            <p v-if="isEditing && product" class="text-xs text-copy-light mt-1">
                                Note: Cannot set product "{{ product.name }}" as its own parent.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="description"
                                class="block text-sm font-medium text-copy mb-1">Description</label>
                            <textarea id="description" v-model="form.description" required rows="6"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                :class="{ 'border-error': form.errors.description }"></textarea>
                            <div v-if="form.errors.description" class="text-xs text-error mt-1">{{
                                form.errors.description }}</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Pricing &
                            Inventory</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="cost" class="block text-sm font-medium text-copy mb-1">Unit Cost (excl.
                                    VAT)</label>
                                <input type="number" id="cost" v-model="form.cost" required min="0.01" step="0.01"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.cost }" />
                                <div v-if="form.errors.cost" class="text-xs text-error mt-1">{{ form.errors.cost }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="stock_qty" class="block text-sm font-medium text-copy mb-1">Stock
                                    Quantity</label>
                                <input type="number" id="stock_qty" v-model="form.stock_qty" required min="0" step="1"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.stock_qty }" />
                                <div v-if="form.errors.stock_qty" class="text-xs text-error mt-1">{{
                                    form.errors.stock_qty }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUCT IMAGES SECTION -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Product Images
                            (Max 5 Total)</h3>

                        <!-- Image Upload Area -->
                        <label for="file-upload" class="block cursor-pointer">
                            <div
                                class="h-24 border-2 border-dashed border-copy-light flex items-center justify-center text-copy-light p-4 rounded-lg bg-secondary-light transition hover:bg-secondary-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Click to select files or drag and drop (Max 2MB per image)</span>
                            </div>
                            <input type="file" id="file-upload" multiple accept="image/jpeg,image/png,image/webp"
                                @change="handleFileUpload" class="hidden" />
                        </label>
                        <div v-if="form.errors['new_images']" class="text-xs text-error mt-1">{{
                            form.errors['new_images'] }}</div>
                        <div v-if="form.errors['new_images.*']" class="text-xs text-error mt-1">{{
                            form.errors['new_images.*'] }}</div>


                        <!-- New Images Queue -->
                        <div v-if="form.new_images.length" class="mt-4 border-t border-copy-light pt-3">
                            <h4 class="text-sm font-bold text-copy mb-2">New Images to Upload ({{ form.new_images.length
                            }})</h4>
                            <ul class="space-y-2">
                                <li v-for="(file, index) in form.new_images" :key="index"
                                    class="flex items-center justify-between p-2 rounded-lg bg-secondary-dark">
                                    <div class="truncate text-sm text-copy">
                                        {{ file.name }}
                                        <span class="text-copy-light ml-2">({{ formatImageSize(file.size) }})</span>
                                    </div>
                                    <button type="button" @click="removeNewImage(index)"
                                        class="text-error hover:text-red-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.72-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Existing Images Grid -->
                        <div v-if="filteredExistingImages.length" class="mt-6 border-t border-copy-light pt-3">
                            <h4 class="text-sm font-bold text-copy mb-2">Existing Images ({{
                                filteredExistingImages.length }})</h4>
                            <!-- Mobile Fix: Switch to grid-cols-1 default for better mobile readability, scaling up to sm:grid-cols-2, etc. -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div v-for="image in filteredExistingImages" :key="image.id"
                                    class="relative rounded-lg overflow-hidden border-2 shadow-md transition" :class="{
                                        'border-success/80': image.is_enabled,
                                        'border-copy-light opacity-50': !image.is_enabled,
                                        'ring-2 ring-error/50': form.images_to_delete.includes(image.id)
                                    }">
                                    <!-- Image Thumbnail -->
                                    <img :src="image.file_path" alt="Product Image" class="w-full h-24 object-cover"
                                        onerror="this.onerror=null;this.src='https://placehold.co/150x96/f0f0f0/666?text=No+Image';" />

                                    <!-- Status Overlay -->
                                    <span v-if="!image.is_enabled"
                                        class="absolute top-0 left-0 bg-error text-error-content text-xs px-2 py-0.5 font-bold">DISABLED</span>

                                    <!-- Action Buttons -->
                                    <div class="absolute bottom-0 right-0 p-1 flex gap-1 bg-black/50 rounded-tl-lg">

                                        <!-- Toggle Button -->
                                        <button type="button" @click="toggleImageStatus(image.id)"
                                            :title="image.is_enabled ? 'Disable Image' : 'Enable Image'"
                                            class="p-1 rounded-full text-white transition hover:scale-110" :class="{
                                                'bg-error': image.is_enabled,
                                                'bg-success': !image.is_enabled
                                            }">
                                            <svg v-if="image.is_enabled" xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M3.707 2.293a1 1 0 00-1.414 1.414L5.586 7H5a1 1 0 000 2h.414l-2.293 2.293a1 1 0 001.414 1.414L7 10.414V11a1 1 0 102 0v-.586l1.293 1.293a1 1 0 001.414-1.414L10.414 9H11a1 1 0 100-2h-.586l-2.293-2.293a1 1 0 10-1.414 1.414L8.586 9H8a1 1 0 00-1 1v.586L4.707 8.707a1 1 0 00-1.414 1.414zM16 9a1 1 0 00-1 1v2a1 1 0 11-2 0v-2a1 1 0 00-2 0v2a3 3 0 003 3h2a3 3 0 003-3v-2a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                <path fill-rule="evenodd"
                                                    d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" @click="deleteExistingImage(image.id)"
                                            title="Mark for Deletion"
                                            class="p-1 rounded-full bg-error text-white transition hover:scale-110"
                                            :class="{ 'opacity-50': form.images_to_delete.includes(image.id) }"
                                            :disabled="form.images_to_delete.includes(image.id)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.72-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END PRODUCT IMAGES SECTION -->


                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">SEO & URL</h3>

                        <div class="mb-4">
                            <label for="meta_title" class="block text-sm font-medium text-copy mb-1">Meta Title</label>
                            <input type="text" id="meta_title" v-model="form.meta_title" maxlength="255"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                :class="{ 'border-error': form.errors.meta_title }" />
                            <div v-if="form.errors.meta_title" class="text-xs text-error mt-1">{{ form.errors.meta_title
                            }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="meta_description" class="block text-sm font-medium text-copy mb-1">Meta
                                Description (Max 500 chars)</label>
                            <textarea id="meta_description" v-model="form.meta_description" rows="3" maxlength="500"
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                :class="{ 'border-error': form.errors.meta_description }"></textarea>
                            <div v-if="form.errors.meta_description" class="text-xs text-error mt-1">{{
                                form.errors.meta_description }}</div>
                        </div>

                        <div class="mb-4">
                            <label for="slug" class="block text-sm font-medium text-copy mb-1">URL Slug</label>
                            <div class="flex items-center">
                                <!-- FIX: Ensure the prefix is visible on all screen sizes -->
                                <span
                                    class="bg-secondary-light border-y-2 border-l-2 border-copy p-3 rounded-l-lg text-copy-light text-sm">/product/</span>
                                <input type="text" id="slug" v-model="form.slug" required
                                    class="flex-grow rounded-r-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'rounded-l-lg': !isEditing, 'border-error': form.errors.slug }" />
                            </div>
                            <div v-if="form.errors.slug" class="text-xs text-error mt-1">{{ form.errors.slug }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Status & Actions
                        </h3>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-copy mb-1">Product Status</label>
                            <select id="status" v-model="form.status" required
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm">
                                <option value="enabled">Enabled (Live)</option>
                                <option value="disabled">Disabled (Draft/Hidden)</option>
                            </select>
                            <div v-if="form.errors.status" class="text-xs text-error mt-1">{{ form.errors.status }}
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="mt-4 w-full py-3 border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background-color: var(--primary); color: var(--primary-content);">
                            {{ form.processing ? 'Saving...' : submitLabel }}
                        </button>

                        <div v-if="form.isDirty" class="mt-2 text-center text-sm text-yellow-600 font-medium">Unsaved
                            changes</div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Categories</h3>

                        <div class="max-h-64 overflow-y-auto pr-2">
                            <ul class="space-y-3">
                                <li v-for="category in categories" :key="category.id">
                                    <label :for="'category-' + category.id"
                                        class="inline-flex items-center gap-3 cursor-pointer transition hover:text-primary-content">
                                        <input type="checkbox" :id="'category-' + category.id" :value="category.id"
                                            :checked="form.category_ids.includes(category.id)"
                                            @change="handleCategoryChange(category.id, ($event.target as HTMLInputElement).checked)"
                                            class="size-5 border-2 border-copy text-primary focus:ring-primary" />
                                        <span class="text-sm text-copy font-medium"> {{ category.name }} </span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <div v-if="form.errors.category_ids" class="text-xs text-error mt-2">{{ form.errors.category_ids
                        }}</div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Couriers</h3>

                        <div class="max-h-64 overflow-y-auto pr-2">
                            <ul class="space-y-3">
                                <li v-for="courier in couriers" :key="courier.id">
                                    <label :for="'courier-' + courier.id"
                                        class="inline-flex items-center gap-3 cursor-pointer transition hover:text-primary-content">
                                        <input type="radio" name="courier" :id="'courier-' + courier.id"
                                            :value="courier.id" :checked="form.courier_id == courier.id"
                                            @change="handleCourierChange(courier.id, true)"
                                            class="size-5 border-2 border-copy text-primary focus:ring-primary" />

                                        <span class="text-sm text-copy font-medium"> {{ courier.type }} - {{
                                            courier.name }} </span>

                                        <div class="mr-2 text-sm text-copy-light"> - £{{ courier.cost.toFixed(2) }}
                                        </div>
                                        <select :id="'courier-status-' + courier.id" v-model="courier.status"
                                            class="size-5 border-2 border-copy text-primary focus:ring-primary mr-2">
                                            <option value="enabled">Enabled</option>
                                            <option value="disabled">Disabled</option>
                                        </select>
                                    </label>
                                </li>
                                <li>
                                    <label for="courier-none"
                                        class="inline-flex items-center gap-3 cursor-pointer transition hover:text-primary-content">
                                        <input type="radio" name="courier" id="courier-none" value=""
                                            :checked="form.courier_id == null" @change="handleCourierChange(0, false)"
                                            class="size-5 border-2 border-copy text-primary focus:ring-primary" />
                                        <span class="text-sm text-copy font-medium"> No Courier Selected </span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
