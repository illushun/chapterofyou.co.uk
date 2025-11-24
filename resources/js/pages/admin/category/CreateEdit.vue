<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { debounce } from 'lodash';

// Interfaces for data structure
interface Category {
    id: number;
    name: string;
    image: string;
    status: 'enabled' | 'disabled';
}

// Define props passed from AdminProductController
const props = defineProps<{
    category?: Category; // Only present when editing
    isEditing: boolean;
    errors: Record<string, string>;
}>();

// Initialize the form state
const form = useForm({
    name: props.category?.name || '',
    status: props.category?.status || 'enabled',

    // Image Handling State
    new_image: File, // File queued for upload
    images_to_delete: [] as number[], // IDs of existing images to delete
    images_to_toggle: [] as number[], // IDs of existing images whose status should be flipped
});

// Computed properties
const title = computed(() => (props.isEditing ? `Edit Category: ${props.category?.name}` : 'Create New Category'));
const submitLabel = computed(() => (props.isEditing ? 'Update Category' : 'Create Category'));

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

// --- Image Management Methods ---

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        Array.from(target.files).forEach(file => {
            form.new_image = file;
        });
        // Reset file input to allow uploading the same file again if needed
        target.value = '';
    }
};

const removeNewImage = (index: number) => {
    form.new_image = null;
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

// --- Form submission logic ---
const submit = () => {
    if (props.isEditing && props.category) {
        // When updating an existing product, files must be sent via POST,
        // and we use transform to inject the _method: 'put' field for method spoofing.
        form.transform((data) => ({
            ...data,
            // Explicitly set _method to 'put' for Laravel to handle multipart/form-data POST as a PUT request
            _method: 'put',
        }))
            // Use form.post() when uploading files, even for PUT requests
            .post(route('admin.categories.update', props.category.id), {
                preserveScroll: true,
                // Reset image arrays after successful submission
                onSuccess: () => {
                    form.new_image = "";
                    form.images_to_delete = [];
                    form.images_to_toggle = [];
                }
            });
    } else {
        // POST request for creation (no method spoofing needed)
        form.post(route('admin.categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.new_image = "";
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
            <p class="text-copy-light mt-1">{{ isEditing ? 'Modify category details' :
                'Enter details for a new category.' }}</p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">General
                            Information</h3>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-copy mb-1">Name</label>
                            <input type="text" id="name" v-model="form.name" required
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                :class="{ 'border-error': form.errors.name }" />
                            <div v-if="form.errors.name" class="text-xs text-error mt-1">{{ form.errors.name }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CATEGORY IMAGE SECTION -->
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Category Image
                        </h3>

                        <!-- Image Upload Area -->
                        <label for="file-upload" class="block cursor-pointer">
                            <div
                                class="h-24 border-2 border-dashed border-copy-light flex items-center justify-center text-copy-light p-4 rounded-lg bg-secondary-light transition hover:bg-secondary-dark">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Click to select file or drag and drop (Max 2MB image)</span>
                            </div>
                            <input type="file" id="file-upload" accept="image/jpeg,image/png,image/webp"
                                @change="handleFileUpload" class="hidden" />
                        </label>
                        <div v-if="form.errors['new_image']" class="text-xs text-error mt-1">{{
                            form.errors['new_image'] }}</div>


                        <!-- New Image Queue -->
                        <div v-if="form.new_image != ''" class="mt-4 border-t border-copy-light pt-3">
                            <h4 class="text-sm font-bold text-copy mb-2">New Image to Upload ({{ form.new_image.length
                                }})</h4>
                            <ul class="space-y-2">
                                <li class="flex items-center justify-between p-2 rounded-lg bg-secondary-dark">
                                    <div class="truncate text-sm text-copy">
                                        {{ form.new_image.name }}
                                        <span class="text-copy-light ml-2">({{ formatImageSize(form.new_image.size)
                                        }})</span>
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

                        <!-- Existing Image Grid -->
                        <div v-if="props.category.image != ''" class="mt-6 border-t border-copy-light pt-3">
                            <h4 class="text-sm font-bold text-copy mb-2">Existing Image</h4>
                            <!-- Mobile Fix: Switch to grid-cols-1 default for better mobile readability, scaling up to sm:grid-cols-2, etc. -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div class="relative rounded-lg overflow-hidden border-2 shadow-md transition border-success/80"
                                    :class="{
                                        'ring-2 ring-error/50': form.images_to_delete.includes(props.category.image)
                                    }">
                                    <!-- Image Thumbnail -->
                                    <img :src="props.category.image" alt="Category Image"
                                        class="w-full h-24 object-cover"
                                        onerror="this.onerror=null;this.src='https://placehold.co/150x96/f0f0f0/666?text=No+Image';" />

                                    <!-- Action Buttons -->
                                    <div class="absolute bottom-0 right-0 p-1 flex gap-1 bg-black/50 rounded-tl-lg">

                                        <!-- Delete Button -->
                                        <button type="button" @click="deleteExistingImage(props.category.image)"
                                            title="Mark for Deletion"
                                            class="p-1 rounded-full bg-error text-white transition hover:scale-110"
                                            :class="{ 'opacity-50': form.images_to_delete.includes(props.category.image) }"
                                            :disabled="form.images_to_delete.includes(props.category.image)">
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
                <!-- END CATEGORY IMAGE SECTION -->
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)]">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Status & Actions
                        </h3>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-copy mb-1">Category Status</label>
                            <select id="status" v-model="form.status" required
                                class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm">
                                <option value="enabled">Enabled</option>
                                <option value="disabled">Disabled</option>
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
            </div>
        </form>
    </AdminLayout>
</template>
