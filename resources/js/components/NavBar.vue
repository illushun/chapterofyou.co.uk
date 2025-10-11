<template>
    <nav class="fixed top-0 left-0 w-full z-20 transition duration-300">
        <div class="bg-white/70 backdrop-blur-lg shadow-lg border-b border-gray-200">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">

                    <div class="flex-shrink-0">
                        <a href="/" class="text-2xl font-black text-gray-900 transition hover:text-sky-600">
                            Chapter of You
                        </a>
                    </div>

                    <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-4">
                        <a
                            href="/"
                            class="text-gray-700 hover:text-sky-600 px-3 py-2 rounded-md text-sm font-medium transition"
                            aria-current="page"
                        >
                            Home
                        </a>
                        <a
                            href="/products"
                            class="text-gray-700 hover:text-sky-600 px-3 py-2 rounded-md text-sm font-medium transition"
                        >
                            Products
                        </a>
                        <a
                            href="/about"
                            class="text-gray-700 hover:text-sky-600 px-3 py-2 rounded-md text-sm font-medium transition"
                        >
                            About Us
                        </a>
                        <a
                            href="/contact"
                            class="text-gray-700 hover:text-sky-600 px-3 py-2 rounded-md text-sm font-medium transition"
                        >
                            Contact
                        </a>
                    </div>

                    <div class="flex items-center gap-3">

                        <button type="button" class="p-2 rounded-full text-gray-500 hover:text-sky-600 transition focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-white" aria-label="Shopping Cart">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </button>

                        <div class="hidden sm:block">
                            <a
                                v-if="$page.props.auth.user"
                                href="/"
                                class="inline-flex items-center px-4 py-2 border border-sky-400 text-sm font-medium rounded-full text-white bg-sky-500 hover:bg-sky-600 transition duration-150 shadow-md"
                            >
                                <svg class="size-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ firstName }}
                            </a>

                            <a
                                v-else
                                :href="login()"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-full text-gray-700 bg-white hover:bg-gray-50 transition duration-150 shadow-sm"
                            >
                                Log In
                            </a>
                        </div>

                        <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="ml-3 inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-sky-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-sky-500 sm:hidden" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <svg v-if="!isMobileMenuOpen" class="block size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <svg v-else class="block size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    </div>
            </div>

            <div :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen}" class="sm:hidden border-t border-gray-200">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a
                        v-if="$page.props.auth.user"
                        href="/"
                        class="block px-3 py-2 rounded-md text-base font-medium text-sky-600 bg-sky-50 hover:bg-sky-100 transition"
                    >
                        Account: {{ firstName }}
                    </a>
                    <a
                        v-else
                        :href="register()"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-sky-600 hover:bg-gray-100 transition"
                    >
                        Log In / Register
                    </a>

                    <a
                        href="/products"
                        class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-sky-600 hover:bg-gray-100 transition"
                    >
                        Products
                    </a>
                    </div>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { login, register } from '@/routes';
import { usePage, Head, Link } from '@inertiajs/vue3';

const isMobileMenuOpen = ref(false);
const page = usePage();

const firstName = computed(() => {
    const user = page.props.auth.user;
    if (user && user.name) {
        const namePart = user.name.split(' ')[0];
        return namePart.charAt(0).toUpperCase() + namePart.slice(1);
    }
    return 'Account';
});
</script>
