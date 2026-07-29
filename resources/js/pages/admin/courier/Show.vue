<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Courier {
    id: number;
    name: string;
    type: 'Royal Mail' | 'FedEx' | 'Evri' | 'DPD';
    status: 'enabled' | 'disabled';
    cost: number;
    created_at: string;
}

defineProps<{
    courier: Courier;
}>();

const formatDate = (dateString: string): string =>
    new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
</script>

<template>
    <AdminLayout>
        <Head :title="`Courier #${courier.id} : Admin`" />

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
                    <span>#{{ courier.id }}</span>
                </div>
                <h1 class="adm-title">{{ courier.name }}</h1>
                <p class="adm-sub">
                    Added {{ formatDate(courier.created_at) }}
                </p>
            </div>
            <span
                class="adm-badge"
                :class="
                    courier.status === 'enabled'
                        ? 'adm-badge--on'
                        : 'adm-badge--off'
                "
            >
                {{ courier.status === 'enabled' ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="adm-form-grid">
            <!-- Left column -->
            <div class="adm-form-left">
                <section class="adm-card">
                    <h2 class="adm-card-title">Courier Details</h2>
                    <div class="adm-field-row">
                        <div class="adm-field">
                            <p class="adm-label--sm">Name</p>
                            <p style="color: var(--adm-ink)">
                                {{ courier.name }}
                            </p>
                        </div>
                        <div class="adm-field">
                            <p class="adm-label--sm">Type</p>
                            <p style="color: var(--adm-ink)">
                                {{ courier.type }}
                            </p>
                        </div>
                        <div class="adm-field">
                            <p class="adm-label--sm">Delivery Charge</p>
                            <p
                                class="adm-td--price"
                                style="color: var(--adm-ink)"
                            >
                                £{{ courier.cost.toFixed(2) }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Right column -->
            <div class="adm-form-right">
                <section class="adm-card adm-card--sticky">
                    <h2 class="adm-card-title">Actions</h2>
                    <Link
                        :href="route('admin.couriers.edit', courier.id)"
                        class="adm-btn adm-btn--primary adm-btn--full"
                    >
                        Edit Courier
                    </Link>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>
