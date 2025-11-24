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

// Define props passed from AdminCategoryController
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
        // When updating an existing category, files must be sent via POST,
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
