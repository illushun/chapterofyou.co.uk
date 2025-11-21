<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import NavBar from '@/components/NavBar.vue';
import SuccessToast from '@/components/ui/coy/toast/SuccessToast.vue';

const successToastRef = ref<InstanceType<typeof SuccessToast> | null>(null);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('contact.store'), {
        onSuccess: () => {
            if (successToastRef.value) {
                successToastRef.value.show('Message Sent!', 'check');
            }
            form.reset('name', 'email', 'subject', 'message');
        },
        onError: (errors) => {
            console.error('Submission failed:', errors);
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <NavBar />

    <Head title="Contact Us" />

    <section class="py-20">
        <div class="mx-auto max-w-screen-xl p-4 md:p-8 lg:p-12">

            <h1 class="text-4xl font-black text-copy mb-4">Get In Touch</h1>
            <p class="text-xl text-copy-light mb-8">
                I'd love to hear from you! Please fill out the form below and i'll respond as quickly as possible.
            </p>

            <form @submit.prevent="submit" class="bg-foreground p-8 rounded-xl border-2 border-copy">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-copy mb-2">Your Name <span
                                class="text-error">*</span></label>
                        <input id="name" type="text" v-model="form.name" required
                            class="w-full rounded-lg border-2 border-copy bg-background p-3 text-copy focus:border-primary-content focus:ring-primary-content"
                            :class="{ 'border-error': form.errors.name }" />
                        <p v-if="form.errors.name" class="text-error text-sm mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-copy mb-2">Your Email <span
                                class="text-error">*</span></label>
                        <input id="email" type="email" v-model="form.email" required
                            class="w-full rounded-lg border-2 border-copy bg-background p-3 text-copy focus:border-primary-content focus:ring-primary-content"
                            :class="{ 'border-error': form.errors.email }" />
                        <p v-if="form.errors.email" class="text-error text-sm mt-1">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="subject" class="block text-sm font-medium text-copy mb-2">Subject (Optional)</label>
                    <input id="subject" type="text" v-model="form.subject"
                        class="w-full rounded-lg border-2 border-copy bg-background p-3 text-copy focus:border-primary-content focus:ring-primary-content"
                        :class="{ 'border-error': form.errors.subject }" />
                    <p v-if="form.errors.subject" class="text-error text-sm mt-1">{{ form.errors.subject }}</p>
                </div>

                <div class="mb-6">
                    <label for="message" class="block text-sm font-medium text-copy mb-2">Message <span
                            class="text-error">*</span></label>
                    <textarea id="message" v-model="form.message" rows="6" required
                        class="w-full rounded-lg border-2 border-copy bg-background p-3 text-copy focus:border-primary-content focus:ring-primary-content"
                        :class="{ 'border-error': form.errors.message }"></textarea>
                    <p v-if="form.errors.message" class="text-error text-sm mt-1">{{ form.errors.message }}</p>
                </div>

                <button type="submit" :disabled="form.processing"
                    class="rounded-lg border-2 border-copy px-8 py-3 text-lg font-bold text-primary-content transition bg-primary hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed">
                    Send Message
                </button>
            </form>
        </div>
    </section>

    <SuccessToast ref="successToastRef" />
</template>
