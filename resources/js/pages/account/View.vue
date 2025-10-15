<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const IconEdit = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`;
const IconCheck = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`;
const IconLock = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
const IconUser = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
const IconMapPin = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;
const IconPlusCircle = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>`;
const IconTrash = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>`;
const IconX = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`;


interface User {
    name: string;
    email: string;
}

interface Address {
    id: number;
    user_id: number;
    type: 'shipping' | 'billing' | 'home';
    is_default: boolean;
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

const isEditingDetails = ref(false);
const detailForm = useForm({
    name: props.user.name,
    email: props.user.email,
});

const saveDetails = () => {
    console.log('Saving Details:', detailForm.data());

    detailForm.put(route('account.profile.update'), { onSuccess: () => isEditingDetails.value = false });
};

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    console.log('Updating Password...');

    passwordForm.put(route('account.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const isAddressModalOpen = ref(false);
const editingAddress = ref<Address | null>(null);

const addressForm = useForm({
    type: 'shipping' as 'shipping' | 'billing' | 'home',
    is_default: false,
    line_1: '',
    line_2: '',
    city: '',
    county: '',
    postcode: '',
    country: 'United Kingdom',
});

const openAddressModal = (address: Address | null = null) => {
    editingAddress.value = address;
    if (address) {
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
        addressForm.reset();
    }
    isAddressModalOpen.value = true;
};

const saveAddress = () => {
    if (editingAddress.value) {
        console.log('Updating address:', editingAddress.value.id, addressForm.data());
        addressForm.put(route('address.update', editingAddress.value.id), {
            onSuccess: () => isAddressModalOpen.value = false,
        });
    } else {
        console.log('Adding new address:', addressForm.data());
        addressForm.post(route('address.store'), {
            onSuccess: () => isAddressModalOpen.value = false,
        });
    }
};

const mockRemoveAddress = (id: number) => {
    if (confirm("Are you sure you want to remove this address?")) {
        console.log('Removing address:', id);
        router.delete(route('address.destroy', id), {
            onSuccess: () => alert('Address removed successfully!'),
        });
    }
}

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


const mockAddresses: Address[] = [
    { id: 1, user_id: 1, type: 'home', is_default: true, line_1: '14 Elm Street', line_2: null, city: 'London', county: 'Greater London', postcode: 'SW1A 0AA', country: 'United Kingdom' },
    { id: 2, user_id: 1, type: 'shipping', is_default: false, line_1: 'Unit 5, Tech Park', line_2: 'Innovation Drive', city: 'Manchester', county: 'Greater Manchester', postcode: 'M1 2AA', country: 'United Kingdom' },
];

const addresses = computed(() => props.addresses || mockAddresses);

</script>

<template>
    <Head title="Account Settings" />

    <div class="min-h-screen bg-background text-copy p-4 md:p-8 lg:p-12">
        <div class="max-w-4xl mx-auto">

            <div class="mb-8 border-b-2 border-copy-light pb-4">
                <h1 class="text-4xl sm:text-5xl font-black text-copy mb-2">My Account</h1>
                <p class="text-lg text-copy-lighter">Manage your personal details, security, and shipping addresses.</p>
            </div>

            <div class="space-y-10">

                <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold text-copy flex items-center gap-2">
                            <div v-html="IconUser" class="size-6 text-primary"></div>
                            General Details
                        </h2>
                        <button
                            @click="isEditingDetails = !isEditingDetails"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition"
                            :class="isEditingDetails ? 'text-error hover:bg-error-light' : 'text-primary hover:bg-primary-light'"
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
                                :disabled="!isEditingDetails"
                                class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy transition disabled:bg-copy-light/20 disabled:border-copy-light"
                            >
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-copy-lighter mb-1">Email Address</label>
                            <input
                                id="email"
                                type="email"
                                v-model="detailForm.email"
                                :disabled="!isEditingDetails"
                                class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy transition disabled:bg-copy-light/20 disabled:border-copy-light"
                            >
                        </div>
                        <div v-if="isEditingDetails" class="sm:col-span-2 flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="detailForm.processing"
                                class="flex items-center gap-2 px-6 py-3 border-2 border-copy rounded-lg font-bold text-primary-content bg-primary hover:bg-primary-dark transition disabled:opacity-50"
                            >
                                <div v-html="IconCheck"></div>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                    <h2 class="text-2xl font-bold text-copy flex items-center gap-2 mb-4">
                        <div v-html="IconLock" class="size-6 text-primary"></div>
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
                        </div>
                        <div class="sm:col-span-3 flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="passwordForm.processing"
                                class="flex items-center gap-2 px-6 py-3 border-2 border-copy rounded-lg font-bold text-primary-content bg-primary hover:bg-primary-dark transition disabled:opacity-50"
                            >
                                <div v-html="IconCheck"></div>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

                <div class="rounded-xl border-2 border-copy bg-foreground shadow-xl p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-copy flex items-center gap-2">
                            <div v-html="IconMapPin" class="size-6 text-primary"></div>
                            My Addresses
                        </h2>
                        <button
                            @click="openAddressModal()"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg text-primary hover:bg-primary-light transition border-2 border-copy"
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

                            <p v-for="line in formatAddress(address)" :key="line" class="text-copy text-base">
                                {{ line }}
                            </p>

                            <div class="flex gap-2 mt-3 pt-3 border-t border-copy-light">
                                <button
                                    @click="openAddressModal(address)"
                                    class="flex items-center gap-1 text-sm text-primary hover:text-primary-dark font-semibold transition"
                                >
                                    <div v-html="IconEdit" class="size-4"></div>
                                    Edit
                                </button>
                                <span class="text-copy-lighter">|</span>
                                <button
                                    @click="mockRemoveAddress(address.id)"
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

    <div v-if="isAddressModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 transition-opacity duration-300" @click.self="isAddressModalOpen = false">
        <div class="relative bg-foreground rounded-xl shadow-2xl max-w-lg w-full m-4 border-2 border-copy">

            <div class="p-6">
                <div class="flex justify-between items-start mb-6 border-b pb-3 border-copy-light">
                    <h3 class="text-2xl font-bold text-copy">
                        {{ editingAddress ? 'Edit Address' : 'Add New Address' }}
                    </h3>
                    <button @click="isAddressModalOpen = false" class="p-1 rounded-full text-copy hover:bg-copy-light transition" aria-label="Close dialog">
                        <div v-html="IconX" class="size-6"></div>
                    </button>
                </div>

                <form @submit.prevent="saveAddress" class="grid grid-cols-1 gap-4">

                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <label for="type" class="block text-sm font-medium text-copy-lighter mb-1">Address Type</label>
                            <select id="type" v-model="addressForm.type" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                                <option value="shipping">Shipping</option>
                                <option value="billing">Billing</option>
                                <option value="home">Home</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-1 sm:pb-3">
                            <label class="flex items-center space-x-2 text-copy font-semibold">
                                <input type="checkbox" v-model="addressForm.is_default" class="size-5 text-primary border-copy rounded">
                                <span>Set as Default</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="line_1" class="block text-sm font-medium text-copy-lighter mb-1">Address Line 1</label>
                        <input id="line_1" type="text" v-model="addressForm.line_1" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                    </div>
                    <div>
                        <label for="line_2" class="block text-sm font-medium text-copy-lighter mb-1">Address Line 2 (Optional)</label>
                        <input id="line_2" type="text" v-model="addressForm.line_2" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-copy-lighter mb-1">City</label>
                            <input id="city" type="text" v-model="addressForm.city" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                        </div>
                        <div>
                            <label for="county" class="block text-sm font-medium text-copy-lighter mb-1">County (Optional)</label>
                            <input id="county" type="text" v-model="addressForm.county" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                        </div>
                        <div>
                            <label for="postcode" class="block text-sm font-medium text-copy-lighter mb-1">Postcode</label>
                            <input id="postcode" type="text" v-model="addressForm.postcode" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                        </div>
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-copy-lighter mb-1">Country</label>
                        <input id="country" type="text" v-model="addressForm.country" class="w-full p-3 border-2 border-copy rounded-lg bg-background text-copy">
                    </div>


                    <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-copy-light">
                        <button
                            type="button"
                            @click="isAddressModalOpen = false"
                            class="px-6 py-2 border-2 border-copy rounded-lg font-semibold text-copy hover:bg-secondary-light transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="addressForm.processing"
                            class="flex items-center px-6 py-2 border-2 border-copy rounded-lg font-bold text-primary-content bg-primary hover:bg-primary-dark transition disabled:opacity-50"
                        >
                            <div v-html="IconCheck" class="size-5 mr-1"></div>
                            {{ editingAddress ? 'Update Address' : 'Save Address' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
