<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import InfiniteMovingCards from '@/components/InfiniteMovingCards.vue';
import HeroSectionOne from '@/components/HeroSectionOne.vue';
import GlowingCard from '@/components/GlowingCard.vue';
import SpringCards from '@/components/ui/coy/SpringCards.vue';
import { Head } from '@inertiajs/vue3';

interface FeaturedCategory {
    name: string;
    href: string;
    image: string;
}

// 🎯 NEW: Interface for promotional images
interface PromotionImage {
    src: string;
    alt: string;
    href: string;
}

interface LandingPageProps {
    promoText: string;
    featuredCategories: FeaturedCategory[];
    promotionImages: PromotionImage[];
}

const props = defineProps<LandingPageProps>();

const testimonials = [
    {
        quote: "The service was exceptional, and the product quality exceeded my expectations. Highly recommend!",
        name: "Alex V.",
        title: "Happy Customer"
    },
    {
        quote: "I love the cute, aesthetic style! Everything brightens up my desk. Will be buying again soon.",
        name: "Sarah K.",
        title: "Style Enthusiast"
    },
    {
        quote: "Fast shipping and perfect packaging. A fantastic experience from start to finish.",
        name: "Jamie L.",
        title: "Verified Buyer"
    },
];
</script>

<template>
    <NavBar />

    <Head title="Aesthetic Store | Cute & Bespoke E-commerce" />

    <style scoped>
    /* Playful wave background for the sections */
    .wave-bg-top {
        clip-path: ellipse(100% 100% at 50% 100%);
    }
    .wave-bg-bottom {
        clip-path: ellipse(100% 100% at 50% 0%);
    }

    /* Keyframe animation for the hero image */
    @keyframes subtleFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .animate-float {
        animation: subtleFloat 3s ease-in-out infinite;
    }
    </style>

    <HeroSectionOne />

    <SpringCards />

    <GlowingCard
        :blur="8"
        :spread="25"
        :proximity="50"
        :disabled="false"
        :glow="true"
        class="mx-auto w-[400px] h-[200px]"
    >
        <div
            class="relative p-6 rounded-xl bg-white text-black shadow-xl w-full h-full flex items-center justify-center"
        >
            <h1 class="text-2xl font-bold z-10">
                Diffuser 1
            </h1>

            </div>
    </GlowingCard>

    <GlowingCard
        :blur="8"
        :spread="25"
        :proximity="50"
        :disabled="false"
        :glow="true"
        class="mx-auto w-[400px] h-[200px]"
    >
        <div
            class="relative p-6 rounded-xl bg-white text-black shadow-xl w-full h-full flex items-center justify-center"
        >
            <h1 class="text-2xl font-bold z-10">
                Diffuser 2
            </h1>

            </div>
    </GlowingCard>

    <GlowingCard
        :blur="8"
        :spread="25"
        :proximity="50"
        :disabled="false"
        :glow="true"
        class="mx-auto w-[400px] h-[200px]"
    >
        <div
            class="relative p-6 rounded-xl bg-white text-black shadow-xl w-full h-full flex items-center justify-center"
        >
            <h1 class="text-2xl font-bold z-10">
                Diffuser 3
            </h1>

            </div>
    </GlowingCard>

    <section class="relative bg-pink-50 pt-16 pb-24 lg:pt-24 lg:pb-40 overflow-hidden">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-center">

                <div class="lg:col-span-6 flex flex-col justify-center text-center lg:text-left">

                    <div v-if="props.promoText" class="mb-6 animate-pulse">
                        <span class="inline-flex items-center rounded-full bg-pink-200 px-4 py-1 text-sm font-bold text-pink-700 tracking-wider shadow-md">
                            ✨ {{ props.promoText }}
                        </span>
                    </div>

                    <h1 class="text-5xl font-extrabold tracking-tighter text-gray-900 sm:text-6xl lg:text-7xl">
                        <span class="block text-sky-600 transition duration-500 hover:text-sky-700">Sweet Styles,</span>
                        <span class="block">Happy Buys.</span>
                    </h1>

                    <p class="mt-6 text-xl text-gray-600 max-w-lg sm:mx-auto lg:mx-0">
                        Handpicked with love. Discover cozy, aesthetic products to brighten your everyday.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row sm:justify-center lg:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                        <PrimaryButton as="a" href="/products" class="w-full sm:w-auto px-8 py-3 text-lg justify-center bg-sky-500 hover:bg-sky-600 transition duration-300 transform hover:scale-105 rounded-xl shadow-lg">
                            Shop All Goodies
                        </PrimaryButton>
                        <PrimaryButton as="a" href="/about" variant="secondary" class="w-full sm:w-auto px-8 py-3 text-lg justify-center border-2 border-pink-300 text-pink-700 bg-white hover:bg-pink-100 transition duration-300 transform hover:scale-105 rounded-xl shadow-md">
                            Read Our Story
                        </PrimaryButton>
                    </div>
                </div>

                <div class="mt-16 lg:mt-0 lg:col-span-6 flex justify-center">
                    <div class="relative w-full max-w-md rounded-3xl overflow-hidden shadow-2xl transition duration-500 hover:shadow-pink-400/50">
                        <img
                            src="/images/hero-product-main.webp"
                            alt="Collection of elegant, handcrafted candles and products"
                            class="w-full h-full object-cover rounded-3xl animate-float"
                            width="800"
                            height="800"
                            loading="eager"
                        />
                         <div class="absolute inset-0 bg-white/10 mix-blend-overlay rounded-3xl pointer-events-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <InfiniteMovingCards
                :items="testimonials"
                direction="right"
                speed="normal"
                :pause-on-hover="true"
                class="mt-6"
            />

    <div class="relative bg-white h-20 -mt-20 z-10 wave-bg-top"></div>
    <section v-if="props.promotionImages && props.promotionImages.length > 0" class="bg-white py-12 sm:py-20 overflow-hidden">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-4xl font-extrabold tracking-tight text-gray-900 mb-12 sm:mb-16">
                Featured Promotions! 🎁
            </h2>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div v-for="(promo, index) in props.promotionImages" :key="index"
                     class="group relative overflow-hidden rounded-2xl shadow-xl transition duration-300 transform hover:scale-[1.03] hover:shadow-2xl hover:shadow-sky-300/50"
                     :class="{
                         'col-span-2 lg:col-span-2': index === 0 && props.promotionImages.length >= 3, // Make first image larger
                     }"
                >
                    <a :href="promo.href" :aria-label="`View ${promo.alt}`">
                        <img
                            :src="promo.src"
                            :alt="promo.alt"
                            class="w-full h-full object-cover transition duration-500 group-hover:rotate-1 group-hover:scale-[1.05] aspect-[4/3] sm:aspect-square lg:aspect-[4/3]"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-sky-400/20 opacity-0 group-hover:opacity-100 transition duration-500 flex items-end p-4">
                            <p class="text-white text-lg font-bold drop-shadow-lg">{{ promo.alt }} &rarr;</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <div class="relative bg-white h-20 -mb-20 z-10 wave-bg-bottom"></div>

    <section class="bg-purple-50 py-16 sm:py-24 pt-40 -mt-20">
        <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl mb-12">
                🌈 Our Cutest Collections
            </h2>

            <div class="grid grid-cols-2 gap-6 lg:grid-cols-4 xl:gap-8">
                <div v-for="category in props.featuredCategories" :key="category.name"
                     class="group relative bg-white p-4 rounded-2xl shadow-lg border border-purple-200 transition duration-300 transform hover:-translate-y-1 hover:shadow-xl hover:shadow-purple-300/50"
                >
                    <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-xl bg-gray-100">
                        <img
                            :src="category.image"
                            :alt="`Image of the ${category.name} category`"
                            class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                            loading="lazy"
                        />
                    </div>

                    <div class="mt-4 text-center">
                        <h3 class="text-lg font-bold text-gray-900 transition group-hover:text-purple-600">
                            <a :href="category.href">
                                <span class="absolute inset-0 z-10" aria-hidden="true"></span>
                                {{ category.name }}
                            </a>
                        </h3>
                        <p class="mt-1 text-sm font-medium text-gray-500">Shop Now</p>
                    </div>
                </div>

                <div class="col-span-2 lg:col-span-1 flex items-center justify-center rounded-2xl border-4 border-dashed border-purple-300 bg-white p-8 transition duration-300 hover:border-sky-500 hover:bg-sky-50">
                    <a href="/categories" class="text-center text-xl font-bold text-gray-600 hover:text-sky-600 transition">
                        See Everything We Offer! 💖 &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>

</template>
