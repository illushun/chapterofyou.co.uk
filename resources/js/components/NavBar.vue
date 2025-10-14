<template>
    <!-- Nav: Bold border and light yellow background (no blur/transparency) -->
    <nav class="fixed top-0 left-0 w-full z-20 transition duration-300 border-b-2 border-black bg-yellow-100 shadow-xl">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                <div class="flex-shrink-0">
                    <a href="/" class="text-3xl font-black text-gray-900 transition hover:text-pink-600">
                        <span class="inline-block border-2 border-black bg-pink-300 px-1.5 leading-none">Coy</span>
                        Store
                    </a>
                </div>

                <div class="hidden sm:ml-6 sm:flex sm:items-center sm:space-x-1">
                    <!-- Desktop Links: Styled for hover block effect -->
                    <a
                        href="/"
                        class="relative text-gray-700 px-3 py-2 text-sm font-bold transition duration-150 group hover:text-black"
                        aria-current="page"
                    >
                        <span class="absolute inset-0 bg-pink-200/0 border-2 border-transparent group-hover:bg-pink-200/80 group-hover:border-black group-hover:shadow-md transition duration-150"></span>
                        <span class="relative z-10">Home</span>
                    </a>
                    <a
                        href="/products"
                        class="relative text-gray-700 px-3 py-2 text-sm font-bold transition duration-150 group hover:text-black"
                    >
                        <span class="absolute inset-0 bg-sky-200/0 border-2 border-transparent group-hover:bg-sky-200/80 group-hover:border-black group-hover:shadow-md transition duration-150"></span>
                        <span class="relative z-10">Products</span>
                    </a>
                    <a
                        href="/about"
                        class="relative text-gray-700 px-3 py-2 text-sm font-bold transition duration-150 group hover:text-black"
                    >
                        <span class="absolute inset-0 bg-purple-200/0 border-2 border-transparent group-hover:bg-purple-200/80 group-hover:border-black group-hover:shadow-md transition duration-150"></span>
                        <span class="relative z-10">About Us</span>
                    </a>
                    <a
                        href="/contact"
                        class="relative text-gray-700 px-3 py-2 text-sm font-bold transition duration-150 group hover:text-black"
                    >
                        <span class="absolute inset-0 bg-green-200/0 border-2 border-transparent group-hover:bg-green-200/80 group-hover:border-black group-hover:shadow-md transition duration-150"></span>
                        <span class="relative z-10">Contact</span>
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Cart Button: Rounded, thick border style -->
                    <button type="button" class="relative p-1 border-2 border-black bg-pink-300 rounded-full transition duration-300 hover:scale-[1.05]" aria-label="Shopping Cart">
                        <div class="p-1 border-2 border-black bg-white rounded-full transition duration-300 hover:bg-pink-50">
                            <svg class="size-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </button>

                    <div class="hidden sm:block">
                        <!-- User/Login Button: Coy Button Style (offset border on hover) -->

                        <!-- Logged In User Account -->
                        <a
                            v-if="$page.props.auth.user"
                            href="/"
                            class="relative inline-flex items-center text-sm font-bold shadow-md transition duration-150 group"
                        >
                            <div class="border-2 border-black bg-sky-700">
                                <span class="block relative -m-0.5 px-4 py-2 border-2 border-black text-white bg-sky-500 transition duration-150 group-hover:translate-x-[-4px] group-hover:translate-y-[-4px]">
                                     <svg class="size-5 mr-1.5 inline-block align-middle" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                     {{ firstName }}
                                </span>
                            </div>
                        </a>

                        <!-- Log In Button -->
                        <a
                            v-else
                            :href="login()"
                            class="relative inline-flex items-center text-sm font-bold shadow-sm transition duration-150 group"
                        >
                            <div class="border-2 border-black bg-gray-400">
                                <span class="block relative -m-0.5 px-4 py-2 border-2 border-black text-gray-700 bg-white transition duration-150 group-hover:translate-x-[-4px] group-hover:translate-y-[-4px]">
                                    Log In
                                </span>
                            </div>
                        </a>
                    </div>

                    <!-- Mobile Menu Button: Bold border, rotate on open -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" type="button" class="ml-3 inline-flex items-center justify-center p-2 rounded-md border-2 border-black bg-white hover:bg-gray-100 transition duration-150 sm:hidden" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg v-if="!isMobileMenuOpen" class="block size-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg v-else class="block size-6 text-gray-800 transform rotate-90 transition duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Panel: Thick border, solid background -->
        <div :class="{'block': isMobileMenuOpen, 'hidden': !isMobileMenuOpen}" class="sm:hidden border-t-2 border-black bg-white">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <!-- Account Status Link -->
                <a
                    v-if="$page.props.auth.user"
                    href="/"
                    class="block px-3 py-2 rounded-md text-base font-bold text-sky-600 border-2 border-sky-400 bg-sky-50 hover:bg-sky-100 transition"
                >
                    Account: {{ firstName }}
                </a>
                <a
                    v-else
                    :href="register()"
                    class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 border-2 border-gray-400 bg-white hover:bg-gray-100 transition"
                >
                    Log In / Register
                </a>

                <!-- Other Mobile Links -->
                <a
                    href="/products"
                    class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 border-2 border-transparent hover:border-black hover:bg-yellow-100 transition"
                >
                    Products
                </a>
                 <a
                    href="/about"
                    class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 border-2 border-transparent hover:border-black hover:bg-yellow-100 transition"
                >
                    About Us
                </a>
                 <a
                    href="/contact"
                    class="block px-3 py-2 rounded-md text-base font-bold text-gray-700 border-2 border-transparent hover:border-black hover:bg-yellow-100 transition"
                >
                    Contact
                </a>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { login, register } from '@/routes';
import { usePage } from '@inertiajs/vue3';

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
