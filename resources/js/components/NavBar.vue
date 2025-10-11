<template>
    <header
        class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
    >
        <nav class="flex items-center justify-end gap-4">
            <Link
                v-if="$page.props.auth.user"
                :href="dashboard()"
                class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
            >
                Dashboard
            </Link>
            <template v-else>
                <Link
                    :href="login()"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Log In
                </Link>
                <Link
                    :href="register()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Register
                </Link>
            </template>
        </nav>
    </header>
</template>

<script setup>
import { ref } from 'vue';

const isMobileMenuOpen = ref(false);
</script>
