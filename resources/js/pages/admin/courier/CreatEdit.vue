<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { debounce } from 'lodash';

// Interfaces for data structure
interface Courier {
    id: number;
    name: string;
    type: 'Royal Mail' | 'FedEx' | 'Evri' | 'DPD';
    status: 'enabled' | 'disabled';
    cost: number;
}

// Define props passed from AdminProductController
const props = defineProps<{
    courier?: Courier; // Only present when editing
    isEditing: boolean;
    errors: Record<string, string>;
}>();

// Initialize the form state
const form = useForm({
    name: props.courier?.name || '',
    type: props.courier?.type || 'Royal Mail',
    status: props.courier?.status || 'enabled',
    cost: props.courier?.cost.toString() || '0.00', //
});

// Computed properties
const title = computed(() => (props.isEditing ? `Edit Courier: ${props.courier?.name}` : 'Create New Courier'));
const submitLabel = computed(() => (props.isEditing ? 'Update Courier' : 'Create Courier'));

// --- Form submission logic ---
const submit = () => {
    // Convert string cost back to number for submission (though Laravel validation handles it)
    form.data().cost = parseFloat(form.cost);

    if (props.isEditing && props.courier) {
        // When updating an existing product, files must be sent via POST,
        // and we use transform to inject the _method: 'put' field for method spoofing.
        form.transform((data) => ({
            ...data,
            // Explicitly set _method to 'put' for Laravel to handle multipart/form-data POST as a PUT request
            _method: 'put',
        }))
            // Use form.post() when uploading files, even for PUT requests
            .post(route('admin.couriers.update', props.courier.id), {
                preserveScroll: true,
                // Reset image arrays after successful submission
                onSuccess: () => { }
            });
    } else {
        // POST request for creation (no method spoofing needed)
        form.post(route('admin.couriers.store'), {
            preserveScroll: true,
            onSuccess: () => { }
        });
    }
};
</script>

<template>
    <AdminLayout>

        <Head :title="title" />

        <div class="mb-6 border-b-2 border-copy pb-2">
            <h2 class="text-3xl font-black">{{ title }}</h2>
            <p class="text-copy-light mt-1">{{ isEditing ? 'Modify courier details' :
                'Enter details for a new courier.' }}</p>
        </div>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">General
                            Information</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-copy mb-1">Name</label>
                                <input type="text" id="name" v-model="form.name" required
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.name }" />
                                <div v-if="form.errors.name" class="text-xs text-error mt-1">{{ form.errors.name }}
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="parent_product_id"
                                    class="block text-sm font-medium text-copy mb-1">Type</label>
                                <select id="type" v-model="form.type"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.type }">
                                    <option :value="Royal Mail">Royal Mail</option>
                                    <option :value="FedEx">FedEx</option>
                                    <option :value="Evri">Evri</option>
                                    <option :value="DPD">DPD</option>
                                </select>
                                <div v-if="form.errors.type" class="text-xs text-error mt-1">{{
                                    form.errors.type }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Pricing</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label for="cost" class="block text-sm font-medium text-copy mb-1">Delivery
                                    Charge</label>
                                <input type="number" id="cost" v-model="form.cost" required min="0.01" step="0.01"
                                    class="w-full rounded-lg border-2 border-copy bg-foreground p-3 text-copy focus:border-primary focus:ring-primary shadow-sm"
                                    :class="{ 'border-error': form.errors.cost }" />
                                <div v-if="form.errors.cost" class="text-xs text-error mt-1">{{ form.errors.cost }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="rounded-xl border-2 border-copy bg-[var(--primary-content)] shadow-xl">
                    <div class="relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-6">
                        <h3 class="text-xl font-bold text-copy mb-4 border-b-2 border-copy-light pb-2">Status & Actions
                        </h3>

                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-copy mb-1">Courier Status</label>
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
