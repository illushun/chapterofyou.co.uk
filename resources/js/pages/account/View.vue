<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import NavBar from '@/components/NavBar.vue';

const IconEdit = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`;
const IconCheck = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`;
const IconLock = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
const IconUser = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
const IconMapPin = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;
const IconPlusCircle = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>`;
const IconTrash = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>`;
const IconX = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`;
const IconSearch = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>`;
const IconArrowRight = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>`;
const IconArrowLeft = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 12-7-7-7 7"/><path d="M12 19V5"/></svg>`;


interface User {
    name: string;
    email: string;
}

interface Address {
    id: number;
    user_id: number;
    type: 'shipping' | 'billing';
    is_default: boolean;
    line_1: string;
    line_2: string | null;
    city: string;
    county: string | null;
    postcode: string;
    country: string;
}

interface LookupAddress {
    line_1: string;
    line_2: string | null;
    city: string;
    county: string | null;
    postcode: string;
    country: string;
}

const props = defineProps<{
    user: User;
    addresses: Address[];
}>();

// General Details Form
const isEditingDetails = ref(false);
const detailForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const saveDetails = () => {
    detailForm.put(route('account.profile.update'), {
        preserveScroll: true,
        onSuccess: () => isEditingDetails.value = false,
    });
};

// Password Form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('account.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

// Address Management State
const isAddressModalOpen = ref(false);
const editingAddress = ref<Address | null>(null);
const modalStep = ref<'lookup' | 'form'>('lookup');
const addressForm = useForm({
    type: 'shipping' as 'shipping' | 'billing',
    is_default: false,
    line_1: '',
    line_2: '',
    city: '',
    county: '',
    postcode: '',
    country: 'United Kingdom',
});

// Address Lookup State
const lookupQuery = ref('');
const lookupResults = ref<LookupAddress[]>([]);
const isLookupLoading = ref(false);
const lookupError = ref('');

// Resets address form and lookup state
const resetAddressModal = () => {
    isAddressModalOpen.value = false;
    editingAddress.value = null;
    modalStep.value = 'lookup';
    addressForm.reset();
    lookupQuery.value = '';
    lookupResults.value = [];
    isLookupLoading.value = false;
    lookupError.value = '';
};

const openAddressModal = (address: Address | null = null) => {
    editingAddress.value = address;

    if (address) {
        // Editing existing address... skip lookup and go straight to form
        modalStep.value = 'form';
        addressForm.defaults({
            type: address.type,
            is_default: address.is_default,
            line_1: address.line_1,
            line_2: address.line_2,
            city: address.city,
            county: address.county,
            postcode: address.postcode,
            country: address.country,
        }).reset();
    } else {
        // Adding new address... start at lookup step
        modalStep.value = 'lookup';
        addressForm.reset();
        addressForm.type = 'shipping';
    }
    isAddressModalOpen.value = true;
};

// Address Lookup
const fetchAddresses = async () => {
    if (!lookupQuery.value || lookupQuery.value.length < 3) {
        lookupError.value = 'Please enter at least 3 characters of your address or postcode.';
        return;
    }

    isLookupLoading.value = true;
    lookupError.value = '';
    lookupResults.value = [];

    try {
        const url = route('address.lookup', { query: lookupQuery.value });
        const response = await fetch(url, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });

        const data = await response.json();
        if (!response.ok) {
            // Handle validation errors (e.g., if the controller throws a 422)
            lookupError.value = data.message || data.errors?.query?.[0] || 'Could not find addresses matching your query.';
            return;
        }

        // Update results
        lookupResults.value = data.addresses || [];
        if (lookupResults.value.length === 0) {
             lookupError.value = 'No addresses found matching your query.';
        }
    } catch (e) {
        lookupError.value = 'An unexpected network error occurred. Check your network or API key.';
        console.error(e);
    } finally {
        isLookupLoading.value = false;
    }
};

const selectAddress = (lookupAddress: LookupAddress) => {
    // Populate form fields from the selected lookup result
    addressForm.line_1 = lookupAddress.line_1;
    addressForm.line_2 = lookupAddress.line_2 || '';
    addressForm.city = lookupAddress.city;
    addressForm.county = lookupAddress.county || '';
    addressForm.postcode = lookupAddress.postcode;
    addressForm.country = lookupAddress.country;

    // Move to the form editing step
    modalStep.value = 'form';
};

const enterAddressManually = () => {
    // Clear lookup results and move to the form
    addressForm.reset();
    addressForm.type = 'shipping';
    modalStep.value = 'form';
}


// Address Save/Edit
const saveAddress = () => {
    const method = editingAddress.value ? 'put' : 'post';
    const url = editingAddress.value
        ? route('address.update', editingAddress.value.id)
        : route('address.store');

    addressForm.submit(method, url, {
        preserveScroll: true,
        onSuccess: () => {
            resetAddressModal();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};

// Format the address for display
const formatAddress = (address: Address): string[] => {
    const lines = [address.line_1];
    if (address.line_2) lines.push(address.line_2);
    lines.push(address.city);
    if (address.county) lines.push(address.county);
    lines.push(address.postcode);
    lines.push(address.country);
    return lines.filter(line => line);
}
</script>

<template>
    <NavBar />

    <Head title="Account Settings" />

    <section class="py-20">

        <div class="mx-auto max-w-screen-xl p-4 md:p-8 lg:p-12">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8 border-b-2 border-copy-light pb-4">
                    <h1 class="text-4xl sm:text-5xl font-black text-copy mb-2">My Account</h1>
                    <p class="text-lg text-copy-lighter">Manage your personal details, security, and shipping addresses.</p>
                </div>

                <div class="space-y-10">

                    <!-- General Details -->
                    <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold text-copy flex items-center gap-2">
                                <div v-html="IconUser" class="size-6 text-primary-content"></div>
                                General Details
                            </h2>
                            <button
                                @click="isEditingDetails = !isEditingDetails"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-primary-content hover:bg-primary-dark transition border-2 border-copy"
                                :class="isEditingDetails ? 'text-error hover:bg-error-light' : 'text-primary-content hover:bg-primary-dark'"
                                aria-label="Toggle editing general details"
                            >
                                <div v-html="isEditingDetails ? IconX : IconEdit"></div>
                                {{ isEditingDetails ? 'Cancel' : 'Edit' }}
                            </button>
                        </div>

                        <form @submit.prevent="saveDetails" class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-copy-lighter mb-1">Name</label>
                                <input
                                    id="name"
                                    type="text"
                                    v-model="detailForm.name"
                                    :disabled="!isEditingDetails || detailForm.processing"
                                    class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy transition disabled:bg-copy-light/20 disabled:border-copy-light"
                                >
                                <p v-if="detailForm.errors.name" class="text-xs text-error mt-1">{{ detailForm.errors.name }}</p>
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-copy-lighter mb-1">Email Address</label>
                                <input
                                    id="email"
                                    type="email"
                                    v-model="detailForm.email"
                                    :disabled="!isEditingDetails || detailForm.processing"
                                    class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy transition disabled:bg-copy-light/20 disabled:border-copy-light"
                                >
                                <p v-if="detailForm.errors.email" class="text-xs text-error mt-1">{{ detailForm.errors.email }}</p>
                            </div>
                            <div v-if="isEditingDetails" class="sm:col-span-2 flex justify-end pt-2">
                                <button
                                    type="submit"
                                    :disabled="detailForm.processing"
                                    class="flex items-center gap-2 px-6 py-3 border-2 border-copy rounded-lg font-bold text-primary-content bg-primary hover:bg-primary-dark transition disabled:opacity-50"
                                >
                                    <div v-html="IconCheck"></div>
                                    {{ detailForm.processing ? 'Saving...' : 'Save Changes' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Update -->
                    <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                        <h2 class="text-2xl font-bold text-copy flex items-center gap-2 mb-4">
                            <div v-html="IconLock" class="size-6 text-primary-content"></div>
                            Update Password
                        </h2>

                        <form @submit.prevent="updatePassword" class="grid sm:grid-cols-3 gap-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-copy-lighter mb-1">Current Password</label>
                                <input
                                    id="current_password"
                                    type="password"
                                    v-model="passwordForm.current_password"
                                    autocomplete="current-password"
                                    class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy"
                                >
                                <p v-if="passwordForm.errors.current_password" class="text-xs text-error mt-1">{{ passwordForm.errors.current_password }}</p>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-copy-lighter mb-1">New Password</label>
                                <input
                                    id="password"
                                    type="password"
                                    v-model="passwordForm.password"
                                    autocomplete="new-password"
                                    class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy"
                                >
                                <p v-if="passwordForm.errors.password" class="text-xs text-error mt-1">{{ passwordForm.errors.password }}</p>
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-copy-lighter mb-1">Confirm New Password</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    v-model="passwordForm.password_confirmation"
                                    autocomplete="new-password"
                                    class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy"
                                >
                                <p v-if="passwordForm.errors.password_confirmation" class="text-xs text-error mt-1">{{ passwordForm.errors.password_confirmation }}</p>
                            </div>
                            <div class="sm:col-span-3 flex justify-end pt-2">
                                <button
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="flex items-center gap-2 px-6 py-3 border-2 border-copy rounded-lg font-bold text-secondary-content bg-secondary hover:bg-secondary-dark transition disabled:opacity-50"
                                >
                                    <div v-html="IconLock"></div>
                                    {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Addresses -->
                    <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-copy flex items-center gap-2">
                                <div v-html="IconMapPin" class="size-6 text-primary-content"></div>
                                My Addresses
                            </h2>
                            <button
                                @click="openAddressModal()"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-primary-content hover:bg-primary-dark transition border-2 border-copy"
                                aria-label="Add new address"
                            >
                                <div v-html="IconPlusCircle"></div>
                                Add New
                            </button>
                        </div>

                        <div v-if="addresses.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="address in addresses"
                                :key="address.id"
                                class="relative p-4 border-2 rounded-lg transition"
                                :class="address.is_default ? 'border-primary bg-primary-light/10 shadow-md' : 'border-copy-light hover:border-copy'"
                            >
                                <div class="text-sm font-bold uppercase mb-1 flex justify-between items-center">
                                    <span :class="address.is_default ? 'text-primary' : 'text-copy-lighter'">{{ address.type }} Address</span>
                                    <span v-if="address.is_default" class="text-xs text-primary bg-primary-light/50 px-2 py-0.5 rounded-full font-extrabold">DEFAULT</span>
                                </div>

                                <!-- Address Lines -->
                                <p v-for="line in formatAddress(address)" :key="line" class="text-copy text-base">
                                    {{ line }}
                                </p>

                                <div class="flex gap-2 mt-3 pt-3 border-t border-copy-light">
                                    <button
                                        @click="openAddressModal(address)"
                                        class="flex items-center gap-1 text-sm text-warning-content hover:text-warning font-semibold transition"
                                    >
                                        <div v-html="IconEdit" class="size-4 text-warning-content"></div>
                                        Edit
                                    </button>
                                    <span class="text-copy-lighter">|</span>
                                    <button
                                        @click="router.delete(route('address.destroy', address.id))"
                                        class="flex items-center gap-1 text-sm text-error hover:text-error-dark font-semibold transition"
                                        :disabled="address.is_default"
                                    >
                                        <div v-html="IconTrash" class="size-4"></div>
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center p-8 border-4 border-dashed border-copy-light rounded-xl bg-background/50">
                            <p class="text-lg text-copy-lighter">You have not added any saved addresses yet.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>

    <!-- Address Add/Edit Modal -->
    <div v-if="isAddressModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 transition-opacity duration-300 p-4" @click.self="resetAddressModal">
        <div class="relative bg-foreground rounded-xl shadow-2xl max-w-lg w-full border-2 border-copy max-h-[90vh] overflow-y-auto">

            <div class="p-6">
                <div class="flex justify-between items-start mb-6 border-b pb-3 border-copy-light sticky top-0 bg-foreground">
                    <h3 class="text-2xl font-bold text-copy">
                        {{ editingAddress ? 'Edit Address' : 'Add New Address' }}
                    </h3>
                    <button @click="resetAddressModal" class="p-1 rounded-full text-copy hover:bg-copy-light transition" aria-label="Close dialog">
                        <div v-html="IconX" class="size-6"></div>
                    </button>
                </div>

                <!-- ADDRESS LOOKUP STEP -->
                <div v-if="modalStep === 'lookup'">
                    <p class="text-copy-lighter mb-4">Start typing your street name, postcode, or house number to find your address quickly.</p>

                    <form @submit.prevent="fetchAddresses" class="flex gap-2 mb-4">
                        <input
                            type="text"
                            v-model="lookupQuery"
                            placeholder="e.g. 10 Downing Street or SW1A 0AA"
                            :disabled="isLookupLoading"
                            class="flex-grow p-3 border-2 border-copy rounded-lg bg-background text-copy"
                        >
                        <button
                            type="submit"
                            :disabled="isLookupLoading || lookupQuery.length < 3"
                            class="flex items-center gap-2 px-4 py-3 border-2 border-copy rounded-lg font-bold text-primary-content bg-primary hover:bg-primary-dark transition disabled:opacity-50"
                        >
                            <div v-html="IconSearch"></div>
                        </button>
                    </form>

                    <p v-if="lookupError" class="text-error text-sm mb-4">{{ lookupError }}</p>
                    <p v-if="isLookupLoading" class="text-primary font-semibold text-center py-4">Searching...</p>

                    <!-- Lookup Results -->
                    <div v-if="lookupResults.length > 0" class="space-y-2 max-h-64 overflow-y-auto border p-2 rounded-lg border-copy-light mb-4">
                        <p class="text-sm font-semibold text-copy-lighter mb-2 border-b pb-2">Select your address:</p>
                        <button
                            v-for="(address, index) in lookupResults" :key="index"
                            @click="selectAddress(address)"
                            class="w-full text-left p-3 rounded-lg border border-copy-light hover:bg-primary-light/20 transition flex justify-between items-center"
                        >
                            <span class="text-copy text-sm">
                                {{ address.line_1 }}{{ address.line_2 ? ', ' + address.line_2 : '' }}, {{ address.city }}
                            </span>
                            <div v-html="IconArrowRight" class="size-4 text-primary"></div>
                        </button>
                    </div>

                    <button
                        @click="enterAddressManually"
                        class="w-full py-3 border-2 border-copy rounded-lg font-semibold text-copy hover:bg-secondary-light transition mt-4"
                    >
                        Enter Address Manually
                    </button>
                </div>


                <!-- ADDRESS FORM STEP -->
                <form v-else-if="modalStep === 'form'" @submit.prevent="saveAddress" class="grid grid-cols-1 gap-4">

                    <!-- Address Type & Default -->
                    <div class="flex flex-col sm:flex-row gap-4 border-b pb-4 border-copy-light">
                        <div class="flex-1">
                            <label for="type" class="block text-sm font-medium text-copy-lighter mb-1">Address Type</label>
                            <select id="type" v-model="addressForm.type" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                                <option value="shipping">Shipping</option>
                                <option value="billing">Billing</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-1 sm:pb-3">
                            <label class="flex items-center space-x-2 text-copy font-semibold">
                                <input type="checkbox" v-model="addressForm.is_default" class="size-5 text-primary border-copy rounded">
                                <span>Set as Default</span>
                            </label>
                        </div>
                    </div>

                    <!-- Address Lines -->
                    <div>
                        <label for="line_1" class="block text-sm font-medium text-copy-lighter mb-1">Address Line 1</label>
                        <input id="line_1" type="text" v-model="addressForm.line_1" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                        <p v-if="addressForm.errors.line_1" class="text-xs text-error mt-1">{{ addressForm.errors.line_1 }}</p>
                    </div>
                    <div>
                        <label for="line_2" class="block text-sm font-medium text-copy-lighter mb-1">Address Line 2 (Optional)</label>
                        <input id="line_2" type="text" v-model="addressForm.line_2" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                        <p v-if="addressForm.errors.line_2" class="text-xs text-error mt-1">{{ addressForm.errors.line_2 }}</p>
                    </div>

                    <!-- City, County, Postcode (Responsive Grid) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-copy-lighter mb-1">City</label>
                            <input id="city" type="text" v-model="addressForm.city" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                            <p v-if="addressForm.errors.city" class="text-xs text-error mt-1">{{ addressForm.errors.city }}</p>
                        </div>
                        <div>
                            <label for="county" class="block text-sm font-medium text-copy-lighter mb-1">County (Optional)</label>
                            <input id="county" type="text" v-model="addressForm.county" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                            <p v-if="addressForm.errors.county" class="text-xs text-error mt-1">{{ addressForm.errors.county }}</p>
                        </div>
                        <div>
                            <label for="postcode" class="block text-sm font-medium text-copy-lighter mb-1">Postcode</label>
                            <input id="postcode" type="text" v-model="addressForm.postcode" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                            <p v-if="addressForm.errors.postcode" class="text-xs text-error mt-1">{{ addressForm.errors.postcode }}</p>
                        </div>
                    </div>

                    <!-- Country -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-copy-lighter mb-1">Country</label>
                        <input id="country" type="text" v-model="addressForm.country" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                        <p v-if="addressForm.errors.country" class="text-xs text-error mt-1">{{ addressForm.errors.country }}</p>
                    </div>

                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-copy-light">
                        <button
                            type="button"
                            @click="modalStep = 'lookup'; addressForm.reset();"
                            class="flex items-center gap-1 text-sm text-copy-lighter hover:text-copy transition"
                        >
                            <div v-html="IconArrowLeft" class="size-4"></div>
                            Back to Lookup
                        </button>
                        <button
                            type="submit"
                            :disabled="addressForm.processing"
                            class="flex items-center px-6 py-2 border-2 border-copy rounded-lg font-bold text-primary-content bg-primary hover:bg-primary-dark transition disabled:opacity-50"
                        >
                            <div v-html="IconCheck" class="size-5 mr-1"></div>
                            {{ addressForm.processing ? 'Saving...' : (editingAddress ? 'Update Address' : 'Save Address') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END: Address Add/Edit Modal -->
</template>
