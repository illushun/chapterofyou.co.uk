<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';

interface FeaturedCategory {
    name: string;
    href: string;
    image: string;
}

interface LandingPageProps {
    promoText: string;
    featuredCategories: FeaturedCategory[];
}

const props = defineProps<LandingPageProps>();
</script>

<template>
    <NavBar />

    <Head title="Chapter of You | Bespoke E-commerce & Services" />
        <section class="relative bg-white pt-16 pb-24 lg:pt-24 lg:pb-40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="lg:grid lg:grid-cols-12 lg:gap-12">

                    <div class="lg:col-span-6 flex flex-col justify-center sm:text-center lg:text-left">

                        <div v-if="props.promoText" class="mb-6">
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-4 py-1 text-sm font-semibold text-indigo-600 tracking-wider">
                                {{ props.promoText }}
                            </span>
                        </div>

                        <h1 class="text-5xl font-extrabold tracking-tight text-gray-900 sm:text-6xl lg:text-7xl xl:text-8xl">
                            <span class="block">Your Story,</span>
                            <span class="block text-indigo-600">Our Craft.</span>
                        </h1>

                        <p class="mt-6 text-xl text-gray-500 max-w-lg sm:mx-auto lg:mx-0">
                            Discover bespoke scents, handcrafted elegance, and personalized well-being services.
                        </p>

                        <div class="mt-10 flex flex-col sm:flex-row sm:justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                            <PrimaryButton as="a" href="/products" class="w-full sm:w-auto px-8 py-3 text-lg justify-center">
                                Shop All Products
                            </PrimaryButton>
                            <PrimaryButton as="a" href="/services" variant="secondary" class="w-full sm:w-auto px-8 py-3 text-lg justify-center border border-indigo-200">
                                Book a Service
                            </PrimaryButton>
                        </div>
                    </div>

                    <div class="mt-16 lg:mt-0 lg:col-span-6">
                        <div class="relative w-full rounded-2xl overflow-hidden shadow-2xl">
                            <img
                                src="/images/hero-product-main.webp"
                                alt="Collection of elegant, handcrafted candles and products"
                                class="w-full h-full object-cover"
                                width="800"
                                height="800"
                                loading="eager"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-gray-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-center text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Explore Our Best Chapters
                </h2>

                <div class="mt-10 grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-3 xl:gap-x-8">
                    <div v-for="category in props.featuredCategories" :key="category.name" class="group relative">
                        <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-gray-200 xl:aspect-h-8 xl:aspect-w-7">
                            <img
                                :src="category.image"
                                :alt="`Image of the ${category.name} category`"
                                class="h-full w-full object-cover object-center group-hover:opacity-75 transition duration-300"
                                loading="lazy"
                            />
                        </div>
                        <h3 class="mt-4 text-sm text-gray-700">
                            <a :href="category.href">
                                <span class="absolute inset-0 z-10" aria-hidden="true"></span>
                                {{ category.name }}
                            </a>
                        </h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">Discover</p>
                    </div>

                    <div class="group relative flex items-center justify-center rounded-lg border-2 border-dashed border-gray-300 hover:border-indigo-500 transition duration-300 p-8">
                        <a href="/categories" class="text-center text-xl font-medium text-gray-500 group-hover:text-indigo-600">
                            View All Categories &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </section>
</template>
