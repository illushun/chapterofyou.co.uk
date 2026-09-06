<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center [overscroll-behavior-y:none] bg-gray-50 p-6 font-serif sm:p-10"
    >
        <div class="w-full max-w-3xl">
            <header class="mb-12 text-center">
                <div class="mb-4 flex justify-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full shadow-xl"
                        style="background-color: #9a7aa0"
                    >
                        <svg
                            class="h-8 w-8 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                    </div>
                </div>
                <h1
                    class="text-5xl font-light tracking-wider uppercase"
                    style="color: #524057"
                >
                    Chapter Of You
                </h1>
                <p class="mt-2 text-lg text-gray-500 italic">
                    Your Chapter, Your Self-Care
                </p>
            </header>

            <main
                class="border-primary-accent rounded-xl border-t-4 bg-white p-10 shadow-2xl sm:p-16"
            >
                <div class="text-center">
                    <p
                        class="mb-3 text-xl font-semibold"
                        style="color: #9a7aa0"
                    >
                        A Moment for Yourself
                    </p>

                    <h2
                        class="mb-6 text-4xl leading-snug font-bold text-gray-900 sm:text-5xl"
                    >
                        Stay tuned, we're turning the page.
                    </h2>

                    <p class="mb-10 text-lg text-gray-600">
                        Launching soon to offer bespoke tailored beauty
                        treatments alongside our hand-crafted aromatherapy
                        diffusers.
                    </p>
                </div>

                <div class="flex justify-center">
                    <form
                        class="w-full max-w-xl"
                        @submit.prevent="submitWaitlist"
                    >
                        <div
                            class="flex flex-col items-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4"
                        >
                            <input
                                type="email"
                                placeholder="Receive an email when our chapter opens"
                                v-model="email"
                                :disabled="isSubmitting"
                                class="w-full flex-1 rounded-full border border-gray-300 px-5 py-3 text-gray-700 shadow-md transition duration-300 placeholder:text-gray-400 focus:border-[#9A7AA0] focus:ring-[#9A7AA0]"
                                required
                            />
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="w-full transform rounded-full px-8 py-3 font-semibold text-white shadow-lg transition duration-300 ease-in-out hover:scale-105 hover:opacity-90 sm:w-auto"
                                style="background-color: #9a7aa0"
                            >
                                {{
                                    isSubmitting
                                        ? 'Subscribing...'
                                        : 'Join the Waitlist'
                                }}
                            </button>
                        </div>

                        <p
                            v-if="message"
                            :class="{
                                'text-green-600': messageType === 'success',
                                'text-red-600': messageType === 'error',
                            }"
                            class="mt-4 text-sm font-medium"
                        >
                            {{ message }}
                        </p>
                    </form>
                </div>
            </main>

            <footer class="mt-12 text-center text-sm text-gray-500">
                <p>
                    &copy; {{ new Date().getFullYear() }} Chapter Of You. All
                    Rights Reserved.
                </p>
                <p class="mt-1">
                    <a
                        href="mailto:contact@chapterofyou.co.uk"
                        class="hover:text-primary-accent transition duration-200"
                        >contact@chapterofyou.co.uk</a
                    >
                </p>
            </footer>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const email = ref('');
const message = ref('');
// 'success' | 'error' is a union type for better safety
const messageType = ref<'success' | 'error' | ''>('');
const isSubmitting = ref(false);

const getCsrfToken = (): string => {
    // Explicitly define the type as HTMLMetaElement | null
    const metaTag: HTMLMetaElement | null = document.head.querySelector(
        'meta[name="csrf-token"]',
    );

    // Type narrowing: ensure metaTag is not null before accessing its content
    if (metaTag && metaTag.content) {
        return metaTag.content;
    }

    // Throw an error if the token is critical but missing
    throw new Error('CSRF token meta tag not found or content is empty.');
};

// Interface for the expected structure of a validation error response from Laravel
interface LaravelValidationErrorResponse {
    message: string;
    errors: {
        email?: string[]; // The 'email' key might exist and contain an array of strings
        // Other fields could be added here if needed
    };
}

// Interface for a generic success or error response
interface GenericResponse {
    message: string;
}

const submitWaitlist = async () => {
    // Clear previous messages
    message.value = '';
    messageType.value = '';
    isSubmitting.value = true;

    try {
        const csrfToken = getCsrfToken(); // Get the token

        const response = await fetch('/waitlist', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                // CRITICAL: Attach the CSRF token to the request headers
                'X-CSRF-TOKEN': csrfToken,
                // Tell Laravel this is an AJAX request
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                email: email.value,
            }),
        });

        // The response data can be either the validation error type or the generic response type
        const data: GenericResponse | LaravelValidationErrorResponse =
            await response.json();

        if (response.ok) {
            // Handle success (status 200 or 201)
            // Type assertion to treat it as GenericResponse for easier access, though it's technically already in data
            const successData = data as GenericResponse;
            message.value =
                successData.message ||
                'Thank you! You have been added to the waitlist.';
            messageType.value = 'success';
            email.value = ''; // Clear the input field
        } else if (response.status === 422) {
            // Handle Laravel validation errors (422 Unprocessable Entity)
            // Type assertion to ensure correct structure
            const errorData = data as LaravelValidationErrorResponse;
            const validationErrors = errorData.errors?.email;

            // Use the first validation error if it exists, otherwise a generic one
            message.value = validationErrors
                ? validationErrors[0]
                : 'Please enter a valid email address.';
            messageType.value = 'error';
        } else {
            // Handle other HTTP errors (e.g., 500, 404)
            message.value =
                'An unexpected server error occurred. Please try again.';
            messageType.value = 'error';
        }
    } catch (error) {
        // Explicitly define 'error' as 'unknown' and use type checks/assertions
        console.error('Fetch error:', error);

        // Provide a meaningful message if the CSRF token was missing
        if (error instanceof Error && error.message.includes('CSRF token')) {
            message.value = error.message;
        } else {
            message.value = 'Could not connect to the server.';
        }

        messageType.value = 'error';
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<style scoped>
:root {
    --primary-color: #9a7aa0;
    --dark-primary-color: #8c6e94;
    /* A slightly darker shade for hover/shadows */
}

/* Custom class for the top card border */
.border-primary-accent {
    border-top-color: var(--primary-color) !important;
}

/* Custom color for the footer link hover */
.hover\:text-primary-accent:hover {
    color: var(--primary-color);
}
</style>
