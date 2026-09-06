<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Courier {
    id: number;
    name: string;
    type: 'Royal Mail' | 'FedEx' | 'Evri' | 'DPD';
    status: 'enabled' | 'disabled';
    cost: number;
}

const props = defineProps<{
    courier?: Courier;
    isEditing: boolean;
    errors: Record<string, string>;
}>();

const form = useForm({
    name: props.courier?.name || '',
    type: props.courier?.type || 'Royal Mail',
    status: props.courier?.status || 'enabled',
    cost: props.courier?.cost.toString() || '0.00',
});

const title = computed(() =>
    props.isEditing ? `Edit Courier: ${props.courier?.name}` : 'New Courier',
);
const submitLabel = computed(() =>
    props.isEditing ? 'Save Changes' : 'Create Courier',
);

const submit = () => {
    if (props.isEditing && props.courier) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(
            route('admin.couriers.update', props.courier.id),
            { preserveScroll: true },
        );
    } else {
        form.post(route('admin.couriers.store'), { preserveScroll: true });
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
                        :href="route('admin.couriers.index')"
                        class="adm-breadcrumb a"
                        >Couriers</Link
                    >
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>{{ isEditing ? 'Edit' : 'New' }}</span>
                </div>
                <h1 class="adm-title">{{ title }}</h1>
                <p class="adm-sub">
                    {{
                        isEditing
                            ? 'Update courier details.'
                            : 'Enter details for a new courier.'
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
            <!-- Left column -->
            <div class="adm-form-left">
                <section class="adm-card">
                    <h2 class="adm-card-title">General Information</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <label class="adm-label" for="name">Name</label>
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
                            <label class="adm-label" for="type">Type</label>
                            <select
                                id="type"
                                v-model="form.type"
                                class="adm-select"
                                :class="{ 'adm-select--err': form.errors.type }"
                            >
                                <option value="Royal Mail">Royal Mail</option>
                                <option value="FedEx">FedEx</option>
                                <option value="Evri">Evri</option>
                                <option value="DPD">DPD</option>
                            </select>
                            <p v-if="form.errors.type" class="adm-err">
                                {{ form.errors.type }}
                            </p>
                        </div>
                    </div>
                </section>

                <section class="adm-card">
                    <h2 class="adm-card-title">Pricing</h2>
                    <div class="adm-field">
                        <label class="adm-label" for="cost"
                            >Delivery Charge</label
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
                                :class="{ 'adm-input--err': form.errors.cost }"
                            />
                        </div>
                        <p v-if="form.errors.cost" class="adm-err">
                            {{ form.errors.cost }}
                        </p>
                    </div>
                </section>
            </div>

            <!-- Right column -->
            <div class="adm-form-right">
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Status &amp; Actions</h2>
                    <div class="adm-field">
                        <label class="adm-label">Courier Status</label>
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
            </div>
        </form>
    </AdminLayout>
</template>
