<script setup lang="ts">
const headline = "Explore your next chapter";
const headlineWords = headline.split(" ");
</script>

<template>
    <div class="relative mx-auto my-10 flex max-w-7xl flex-col items-center justify-center">
        <div class="absolute inset-y-0 left-0 h-full w-px bg-neutral-200/80 dark:bg-neutral-800/80">
            <div class="absolute top-0 h-40 w-px bg-gradient-to-b from-transparent via-blue-500 to-transparent" />
        </div>

        <div class="absolute inset-y-0 right-0 h-full w-px bg-neutral-200/80 dark:bg-neutral-800/80">
            <div class="absolute h-40 w-px bg-gradient-to-b from-transparent via-blue-500 to-transparent" />
        </div>

        <div class="absolute inset-x-0 bottom-0 h-px w-full bg-neutral-200/80 dark:bg-neutral-800/80">
            <div class="absolute mx-auto h-px w-40 bg-gradient-to-r from-transparent via-blue-500 to-transparent" />
        </div>

        <div class="px-4 py-10 md:py-20">
            <h1 class="relative z-10 mx-auto max-w-4xl text-center text-2xl font-bold text-slate-700 md:text-4xl lg:text-7xl dark:text-slate-300">
                <span
                    v-for="(word, index) in headlineWords"
                    :key="index"
                    class="mr-2 inline-block"

                    v-motion
                    :initial="{ opacity: 0, filter: 'blur(4px)', y: 10 }"
                    :enter="{ opacity: 1, filter: 'blur(0px)', y: 0 }"
                    :delay="index * 100"
                    :transition="{
                        duration: 300,
                        ease: 'easeInOut'
                    }"
                >
                    {{ word }}
                </span>
            </h1>

            <p
                v-motion
                :initial="{ opacity: 0 }"
                :enter="{ opacity: 1 }"
                :delay="800"
                :transition="{ duration: 300 }"
                class="relative z-10 mx-auto max-w-xl py-4 text-center text-lg font-normal text-neutral-600 dark:text-neutral-400"
            >
                Launching soon to offer bespoke tailored beauty treatments alongside our hand-crafted aromatherapy diffusers.
            </p>

            <div
                v-motion
                :initial="{ opacity: 0 }"
                :enter="{ opacity: 1 }"
                :delay="1000"
                :transition="{ duration: 300 }"
                class="relative z-10 mt-8 flex flex-wrap items-center justify-center gap-4"
            >
                <button class="w-60 transform rounded-lg bg-black px-6 py-2 font-medium text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-gray-800 dark:bg-white dark:text-black dark:hover:bg-gray-200">
                    Explore Now
                </button>
                <button class="w-60 transform rounded-lg border border-gray-300 bg-white px-6 py-2 font-medium text-black transition-all duration-300 hover:-translate-y-0.5 hover:bg-gray-100 dark:border-gray-700 dark:bg-black dark:text-white dark:hover:bg-gray-900">
                    Contact
                </button>
            </div>

            <div
                v-motion
                :initial="{ opacity: 0, y: 10 }"
                :enter="{ opacity: 1, y: 0 }"
                :delay="1200"
                :transition="{ duration: 300 }"
                class="relative z-10 mt-20 rounded-3xl border border-neutral-200 bg-neutral-100 p-4 shadow-md dark:border-neutral-800 dark:bg-neutral-900"
            >
                <div class="w-full overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700">
                    <img
                        src="https://eu.neomwellbeing.com/cdn/shop/articles/benefits-of-diffusers.jpg?v=1715589869&width=1920"
                        alt="Landing page preview"
                        class="aspect-[16/9] h-auto w-full object-cover"
                        height="1000"
                        width="1000"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
