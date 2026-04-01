<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import NavBar from '@/components/NavBar.vue';

const IconEdit = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`;
const IconCheck = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`;
const IconLock = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>`;
const IconUser = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`;
const IconMapPin = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>`;
const IconPlus = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>`;
const IconTrash = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>`;
const IconX = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`;
const IconSearch = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>`;
const IconArrowRight = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>`;
const IconArrowLeft = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m19 12-7-7-7 7"/><path d="M12 19V5"/></svg>`;

interface User { name: string; email: string; }
interface Address {
    id: number; user_id: number; type: 'shipping' | 'billing';
    is_default: boolean; line_1: string; line_2: string | null;
    city: string; county: string | null; postcode: string; country: string;
}
interface LookupAddress {
    line_1: string; line_2: string | null; city: string;
    county: string | null; postcode: string; country: string;
}

const props = defineProps<{ user: User; addresses: Address[]; }>();

// ── General details ────────────────────────────────────────────────────────
const isEditingDetails = ref(false);
const detailForm = useForm({ name: props.user.name, email: props.user.email });
const saveDetails = () => {
    detailForm.put(route('account.profile.update'), {
        preserveScroll: true,
        onSuccess: () => isEditingDetails.value = false,
    });
};

// ── Password ───────────────────────────────────────────────────────────────
const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
const updatePassword = () => {
    passwordForm.put(route('account.password.update'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

// ── Addresses ──────────────────────────────────────────────────────────────
const isAddressModalOpen = ref(false);
const editingAddress = ref<Address | null>(null);
const modalStep = ref<'lookup' | 'form'>('lookup');
const addressForm = useForm({
    type: 'shipping' as 'shipping' | 'billing',
    is_default: false, line_1: '', line_2: '',
    city: '', county: '', postcode: '', country: 'United Kingdom',
});

const lookupQuery = ref('');
const lookupResults = ref<LookupAddress[]>([]);
const isLookupLoading = ref(false);
const lookupError = ref('');

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
        modalStep.value = 'form';
        addressForm.defaults({
            type: address.type, is_default: address.is_default,
            line_1: address.line_1, line_2: address.line_2,
            city: address.city, county: address.county,
            postcode: address.postcode, country: address.country,
        }).reset();
    } else {
        modalStep.value = 'lookup';
        addressForm.reset();
        addressForm.type = 'shipping';
    }
    isAddressModalOpen.value = true;
};

const fetchAddresses = async () => {
    if (!lookupQuery.value || lookupQuery.value.length < 3) {
        lookupError.value = 'Please enter at least 3 characters.';
        return;
    }
    isLookupLoading.value = true;
    lookupError.value = '';
    lookupResults.value = [];
    try {
        const url = route('address.lookup', { query: lookupQuery.value });
        const res = await fetch(url, { headers: { 'Content-Type': 'application/json' } });
        const data = await res.json();
        if (!res.ok) { lookupError.value = data.message || 'Could not find addresses.'; return; }
        lookupResults.value = data.addresses || [];
        if (!lookupResults.value.length) lookupError.value = 'No addresses found.';
    } catch { lookupError.value = 'A network error occurred.'; }
    finally { isLookupLoading.value = false; }
};

const selectAddress = (a: LookupAddress) => {
    addressForm.line_1 = a.line_1; addressForm.line_2 = a.line_2 || '';
    addressForm.city = a.city; addressForm.county = a.county || '';
    addressForm.postcode = a.postcode; addressForm.country = a.country;
    modalStep.value = 'form';
};

const enterAddressManually = () => {
    addressForm.reset(); addressForm.type = 'shipping'; modalStep.value = 'form';
};

const saveAddress = () => {
    const method = editingAddress.value ? 'put' : 'post';
    const url = editingAddress.value
        ? route('address.update', editingAddress.value.id)
        : route('address.store');
    addressForm.submit(method, url, {
        preserveScroll: true,
        onSuccess: () => resetAddressModal(),
    });
};

const formatAddress = (a: Address): string[] =>
    [a.line_1, a.line_2, a.city, a.county, a.postcode, a.country].filter(Boolean) as string[];
</script>

<template>
    <NavBar />

    <Head title="My Account" />

    <component :is="'link'"
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Nunito:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <main class="ap">
        <div class="ap-wrap">

            <!-- Header -->
            <header class="ap-header">
                <h1 class="ap-title">My Account</h1>
                <p class="ap-sub">Manage your details, password and saved addresses.</p>
            </header>

            <div class="ap-sections">

                <!-- ── General Details ── -->
                <section class="ap-card">
                    <div class="ap-card-head">
                        <h2 class="ap-card-title">
                            <span class="ap-icon" v-html="IconUser"></span>
                            Personal Details
                        </h2>
                        <button @click="isEditingDetails = !isEditingDetails" class="btn-ghost"
                            :class="{ 'btn-ghost--cancel': isEditingDetails }">
                            <span v-html="isEditingDetails ? IconX : IconEdit"></span>
                            {{ isEditingDetails ? 'Cancel' : 'Edit' }}
                        </button>
                    </div>

                    <form @submit.prevent="saveDetails" class="ap-form">
                        <div class="field-group">
                            <div class="field">
                                <label for="name" class="field-label">Full Name</label>
                                <input id="name" type="text" v-model="detailForm.name"
                                    :disabled="!isEditingDetails || detailForm.processing" class="field-input" />
                                <p v-if="detailForm.errors.name" class="field-error">{{ detailForm.errors.name }}</p>
                            </div>
                            <div class="field">
                                <label for="email" class="field-label">Email Address</label>
                                <input id="email" type="email" v-model="detailForm.email"
                                    :disabled="!isEditingDetails || detailForm.processing" class="field-input" />
                                <p v-if="detailForm.errors.email" class="field-error">{{ detailForm.errors.email }}</p>
                            </div>
                        </div>
                        <div v-if="isEditingDetails" class="ap-form-footer">
                            <button type="submit" :disabled="detailForm.processing" class="btn-rose">
                                <span v-html="IconCheck"></span>
                                {{ detailForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- ── Password ── -->
                <section class="ap-card">
                    <div class="ap-card-head">
                        <h2 class="ap-card-title">
                            <span class="ap-icon" v-html="IconLock"></span>
                            Update Password
                        </h2>
                    </div>

                    <form @submit.prevent="updatePassword" class="ap-form">
                        <div class="field-group field-group--3">
                            <div class="field">
                                <label for="current_password" class="field-label">Current Password</label>
                                <input id="current_password" type="password" v-model="passwordForm.current_password"
                                    autocomplete="current-password" class="field-input" />
                                <p v-if="passwordForm.errors.current_password" class="field-error">{{
                                    passwordForm.errors.current_password }}</p>
                            </div>
                            <div class="field">
                                <label for="password" class="field-label">New Password</label>
                                <input id="password" type="password" v-model="passwordForm.password"
                                    autocomplete="new-password" class="field-input" />
                                <p v-if="passwordForm.errors.password" class="field-error">{{
                                    passwordForm.errors.password }}</p>
                            </div>
                            <div class="field">
                                <label for="password_confirmation" class="field-label">Confirm New Password</label>
                                <input id="password_confirmation" type="password"
                                    v-model="passwordForm.password_confirmation" autocomplete="new-password"
                                    class="field-input" />
                                <p v-if="passwordForm.errors.password_confirmation" class="field-error">{{
                                    passwordForm.errors.password_confirmation }}</p>
                            </div>
                        </div>
                        <div class="ap-form-footer">
                            <button type="submit" :disabled="passwordForm.processing" class="btn-rose">
                                <span v-html="IconLock"></span>
                                {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                            </button>
                        </div>
                    </form>
                </section>

                <!-- ── Addresses ── -->
                <section class="ap-card">
                    <div class="ap-card-head">
                        <h2 class="ap-card-title">
                            <span class="ap-icon" v-html="IconMapPin"></span>
                            Saved Addresses
                        </h2>
                        <button @click="openAddressModal()" class="btn-ghost">
                            <span v-html="IconPlus"></span>
                            Add New
                        </button>
                    </div>

                    <div v-if="addresses.length" class="address-grid">
                        <div v-for="address in addresses" :key="address.id" class="address-card"
                            :class="{ 'address-card--default': address.is_default }">

                            <!-- petal watermarks -->
                            <span class="addr-petal-bl" aria-hidden="true"></span>
                            <span class="addr-petal-tr" aria-hidden="true"></span>

                            <div class="address-card-head">
                                <span class="address-type">{{ address.type }}</span>
                                <span v-if="address.is_default" class="address-default-badge">Default</span>
                            </div>

                            <address class="address-lines">
                                <span v-for="line in formatAddress(address)" :key="line">{{ line }}</span>
                            </address>

                            <div class="address-actions">
                                <button @click="openAddressModal(address)" class="addr-action-btn">
                                    <span v-html="IconEdit"></span> Edit
                                </button>
                                <span class="addr-divider" aria-hidden="true"></span>
                                <button @click="router.delete(route('address.destroy', address.id))"
                                    class="addr-action-btn addr-action-btn--danger" :disabled="address.is_default">
                                    <span v-html="IconTrash"></span> Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="address-empty">
                        <p>No saved addresses yet.</p>
                        <button @click="openAddressModal()" class="btn-rose btn-rose--sm">Add your first
                            address</button>
                    </div>
                </section>

            </div>
        </div>
    </main>

    <!-- ── Address Modal ── -->
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="isAddressModalOpen" class="modal-backdrop" @click.self="resetAddressModal">
                <div class="modal-box">

                    <!-- Modal header -->
                    <div class="modal-head">
                        <h3 class="modal-title">
                            {{ editingAddress ? 'Edit Address' : 'Add Address' }}
                        </h3>
                        <button @click="resetAddressModal" class="modal-close" aria-label="Close">
                            <span v-html="IconX"></span>
                        </button>
                    </div>

                    <!-- Step: Lookup -->
                    <div v-if="modalStep === 'lookup'" class="modal-body">
                        <p class="modal-hint">Enter your postcode or street to find your address quickly.</p>

                        <form @submit.prevent="fetchAddresses" class="lookup-form">
                            <input type="text" v-model="lookupQuery" placeholder="e.g. SW1A 0AA or 10 Downing Street"
                                :disabled="isLookupLoading" class="field-input" />
                            <button type="submit" :disabled="isLookupLoading || lookupQuery.length < 3"
                                class="btn-rose btn-rose--icon">
                                <span v-html="IconSearch"></span>
                            </button>
                        </form>

                        <p v-if="lookupError" class="field-error mt-2">{{ lookupError }}</p>
                        <p v-if="isLookupLoading" class="lookup-searching">Searching...</p>

                        <div v-if="lookupResults.length" class="lookup-results">
                            <p class="lookup-results-label">Select your address</p>
                            <button v-for="(a, i) in lookupResults" :key="i" @click="selectAddress(a)"
                                class="lookup-result-row">
                                <span>{{ a.line_1 }}{{ a.line_2 ? ', ' + a.line_2 : '' }}, {{ a.city }}</span>
                                <span v-html="IconArrowRight"></span>
                            </button>
                        </div>

                        <button @click="enterAddressManually" class="btn-outline w-full mt-4">
                            Enter address manually
                        </button>
                    </div>

                    <!-- Step: Form -->
                    <form v-else-if="modalStep === 'form'" @submit.prevent="saveAddress" class="modal-body">

                        <div class="field-row">
                            <div class="field">
                                <label for="m-type" class="field-label">Address Type</label>
                                <select id="m-type" v-model="addressForm.type" class="field-input">
                                    <option value="shipping">Shipping</option>
                                    <option value="billing">Billing</option>
                                </select>
                            </div>
                            <div class="field field--checkbox">
                                <label class="checkbox-label">
                                    <input type="checkbox" v-model="addressForm.is_default" class="checkbox-input" />
                                    <span>Set as default</span>
                                </label>
                            </div>
                        </div>

                        <div class="field">
                            <label for="m-line1" class="field-label">Address Line 1</label>
                            <input id="m-line1" type="text" v-model="addressForm.line_1" class="field-input" />
                            <p v-if="addressForm.errors.line_1" class="field-error">{{ addressForm.errors.line_1 }}</p>
                        </div>

                        <div class="field">
                            <label for="m-line2" class="field-label">Address Line 2 <span
                                    class="field-optional">(optional)</span></label>
                            <input id="m-line2" type="text" v-model="addressForm.line_2" class="field-input" />
                        </div>

                        <div class="field-row field-row--3">
                            <div class="field">
                                <label for="m-city" class="field-label">City</label>
                                <input id="m-city" type="text" v-model="addressForm.city" class="field-input" />
                                <p v-if="addressForm.errors.city" class="field-error">{{ addressForm.errors.city }}</p>
                            </div>
                            <div class="field">
                                <label for="m-county" class="field-label">County <span
                                        class="field-optional">(optional)</span></label>
                                <input id="m-county" type="text" v-model="addressForm.county" class="field-input" />
                            </div>
                        </div>

                        <div class="field">
                            <label for="m-postcode" class="field-label">Postcode</label>
                            <input id="m-postcode" type="text" v-model="addressForm.postcode" class="field-input" />
                            <p v-if="addressForm.errors.postcode" class="field-error">{{ addressForm.errors.postcode
                            }}</p>
                        </div>

                        <div class="field">
                            <label for="m-country" class="field-label">Country</label>
                            <input id="m-country" type="text" v-model="addressForm.country" class="field-input" />
                        </div>

                        <div class="modal-form-footer">
                            <button type="button" @click="modalStep = 'lookup'" class="btn-text">
                                <span v-html="IconArrowLeft"></span> Back
                            </button>
                            <button type="submit" :disabled="addressForm.processing" class="btn-rose">
                                <span v-html="IconCheck"></span>
                                {{ addressForm.processing ? 'Saving...' : (editingAddress ? 'Update' : 'Save Address')
                                }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* ── Base ── */
.ap {
    font-family: 'Nunito', sans-serif;
    min-height: 100vh;
    padding-top: 64px;
    background: #fdf4f3;
    color: #2d1a1a;
}

.ap-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

/* ── Header ── */
.ap-header {
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5c9c7;
}

.ap-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: clamp(2rem, 5vw, 2.8rem);
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    margin-bottom: 0.25rem;
}

.ap-sub {
    font-size: 0.95rem;
    color: #6b4f4f;
}

/* ── Section cards ── */
.ap-sections {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.ap-card {
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    background: #fffafa;
    box-shadow: 0 2px 16px rgba(229, 201, 199, 0.35);
    padding: 1.75rem;
    position: relative;
    overflow: hidden;
}

.ap-card::before {
    content: '✿';
    position: absolute;
    bottom: -6px;
    right: 8px;
    font-size: 3.5rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.ap-card::after {
    content: '✿';
    position: absolute;
    top: 6px;
    left: 10px;
    font-size: 0.9rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.ap-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5c9c7;
}

.ap-card-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ap-icon {
    color: #8c4a50;
    display: flex;
    align-items: center;
}

/* ── Forms ── */
.ap-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ap-form-footer {
    display: flex;
    justify-content: flex-end;
    padding-top: 0.5rem;
}

.field-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.field-group--3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 640px) {

    .field-group,
    .field-group--3 {
        grid-template-columns: 1fr;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #6b4f4f;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.field-optional {
    font-weight: 400;
    text-transform: none;
    font-style: italic;
}

.field-input {
    padding: 0.7rem 0.9rem;
    border: 1px solid #e5c9c7;
    border-radius: 10px;
    background: #fdf4f3;
    color: #2d1a1a;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}

.field-input:focus {
    border-color: #8c4a50;
    box-shadow: 0 0 0 3px rgba(140, 74, 80, 0.1);
}

.field-input:disabled {
    background: #f5eceb;
    color: #9a7070;
    cursor: not-allowed;
}

.field-error {
    font-size: 0.8rem;
    color: #b54040;
    margin-top: 0.1rem;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    align-items: end;
}

.field-row--3 {
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 540px) {

    .field-row,
    .field-row--3 {
        grid-template-columns: 1fr;
    }
}

.field--checkbox {
    justify-content: flex-end;
    padding-bottom: 0.2rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.92rem;
    color: #2d1a1a;
    cursor: pointer;
}

.checkbox-input {
    width: 16px;
    height: 16px;
    accent-color: #8c4a50;
    cursor: pointer;
}

/* ── Buttons ── */
.btn-rose {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1.4rem;
    border-radius: 999px;
    border: 1px solid #a85058;
    background: linear-gradient(135deg, #c47078, #a85058);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
}

.btn-rose:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.btn-rose:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-rose--sm {
    padding: 0.5rem 1.1rem;
    font-size: 0.85rem;
}

.btn-rose--icon {
    padding: 0.65rem 0.9rem;
}

.btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    border: 1px solid #e5c9c7;
    background: transparent;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
}

.btn-ghost:hover {
    background: #f5e4e4;
    border-color: #c9a4a4;
    color: #2d1a1a;
}

.btn-ghost--cancel {
    color: #8c4a50;
    border-color: #c9a4a4;
}

.btn-ghost--cancel:hover {
    background: #faeaea;
}

.btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.65rem 1.25rem;
    border-radius: 10px;
    border: 1px solid #e5c9c7;
    background: transparent;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}

.btn-outline:hover {
    background: #f5e4e4;
    border-color: #c9a4a4;
}

.btn-text {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: none;
    border: none;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.88rem;
    cursor: pointer;
    transition: color 0.2s;
    padding: 0;
}

.btn-text:hover {
    color: #2d1a1a;
}

.w-full {
    width: 100%;
}

/* ── Address grid ── */
.address-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 600px) {
    .address-grid {
        grid-template-columns: 1fr;
    }
}

.address-card {
    border: 1px solid #e5c9c7;
    border-radius: 16px;
    background: #fdf4f3;
    padding: 1.1rem 1.2rem;
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.2s;
}

.address-card:hover {
    box-shadow: 0 4px 18px rgba(229, 201, 199, 0.5);
}

.address-card--default {
    border-color: #8c4a50;
    background: #fff5f5;
    box-shadow: 0 2px 12px rgba(140, 74, 80, 0.1);
}

/* petal watermarks on address cards */
.addr-petal-bl::before,
.addr-petal-tr::before {
    pointer-events: none;
    user-select: none;
}

.address-card::before {
    content: '✿';
    position: absolute;
    bottom: -4px;
    right: 6px;
    font-size: 2.8rem;
    color: #c9a4a4;
    opacity: 0.12;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.address-card::after {
    content: '✿';
    position: absolute;
    top: 5px;
    left: 8px;
    font-size: 0.75rem;
    color: #c9a4a4;
    opacity: 0.22;
    pointer-events: none;
    user-select: none;
    line-height: 1;
}

.address-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.6rem;
}

.address-type {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #8c4a50;
}

.address-default-badge {
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #8c4a50;
    background: rgba(140, 74, 80, 0.1);
    border: 1px solid rgba(140, 74, 80, 0.2);
    border-radius: 999px;
    padding: 0.15rem 0.55rem;
}

.address-lines {
    font-style: normal;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    font-size: 0.9rem;
    color: #2d1a1a;
    line-height: 1.5;
    margin-bottom: 0.75rem;
}

.address-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding-top: 0.65rem;
    border-top: 1px solid #e5c9c7;
}

.addr-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: none;
    border: none;
    font-family: 'Nunito', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    color: #6b4f4f;
    cursor: pointer;
    transition: color 0.2s;
    padding: 0;
}

.addr-action-btn:hover {
    color: #2d1a1a;
}

.addr-action-btn--danger:hover {
    color: #b54040;
}

.addr-action-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.addr-divider {
    width: 1px;
    height: 14px;
    background: #e5c9c7;
    flex-shrink: 0;
}

.address-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    border: 1.5px dashed #e5c9c7;
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.address-empty p {
    font-size: 0.95rem;
    color: #6b4f4f;
    font-style: italic;
}

/* ── Modal ── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(45, 26, 26, 0.45);
    backdrop-filter: blur(3px);
    padding: 1rem;
}

.modal-box {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(45, 26, 26, 0.2);
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.4rem 1.5rem 1rem;
    border-bottom: 1px solid #e5c9c7;
    position: sticky;
    top: 0;
    background: #fffafa;
    z-index: 1;
}

.modal-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    font-style: italic;
    font-weight: 400;
    color: #2d1a1a;
}

.modal-close {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #e5c9c7;
    background: transparent;
    color: #6b4f4f;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.modal-close:hover {
    background: #f5e4e4;
    color: #8c4a50;
}

.modal-body {
    padding: 1.25rem 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.modal-hint {
    font-size: 0.9rem;
    color: #6b4f4f;
    line-height: 1.5;
}

.modal-form-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.75rem;
    border-top: 1px solid #e5c9c7;
    margin-top: 0.25rem;
}

/* Lookup */
.lookup-form {
    display: flex;
    gap: 0.6rem;
}

.lookup-form .field-input {
    flex: 1;
}

.lookup-searching {
    font-size: 0.9rem;
    color: #8c4a50;
    font-style: italic;
    text-align: center;
}

.lookup-results {
    border: 1px solid #e5c9c7;
    border-radius: 12px;
    overflow: hidden;
    max-height: 220px;
    overflow-y: auto;
}

.lookup-results-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b4f4f;
    padding: 0.6rem 0.9rem;
    background: #fdf4f3;
    border-bottom: 1px solid #e5c9c7;
}

.lookup-result-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 0.75rem 0.9rem;
    background: transparent;
    border: none;
    border-bottom: 1px solid #f0e4e2;
    font-family: 'Nunito', sans-serif;
    font-size: 0.88rem;
    color: #2d1a1a;
    text-align: left;
    cursor: pointer;
    transition: background 0.15s;
    gap: 0.5rem;
}

.lookup-result-row:last-child {
    border-bottom: none;
}

.lookup-result-row:hover {
    background: #faeaea;
}

.lookup-result-row span:last-child {
    color: #8c4a50;
    flex-shrink: 0;
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.25s ease;
}

.modal-enter-active .modal-box,
.modal-leave-active .modal-box {
    transition: transform 0.25s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-box {
    transform: translateY(16px) scale(0.98);
}

.modal-leave-to .modal-box {
    transform: translateY(8px) scale(0.99);
}

/* mt utility */
.mt-2 {
    margin-top: 0.5rem;
}

.mt-4 {
    margin-top: 1rem;
}
</style>
