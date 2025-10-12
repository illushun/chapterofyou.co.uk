<script setup lang="ts">
import FloatingAnimatedNavbar from '@/components/navigation/AnimatedNavBar.vue';

// Define the navigation links
const navItems = [
    { name: "Home", link: "/" },
    { name: "Products", link: "/products" },
    { name: "Services", link: "/services" },
    { name: "Contact", link: "/contact" },
];
const Navbar = FloatingAnimatedNavbar;

const {
    NavbarLogo,
    NavBody,
    NavItems,
    NavbarButton,
    MobileNav,
    MobileNavHeader,
    MobileNavMenu,
    MobileNavToggle
} = Navbar;

</script>

<template>
    <div class="h-screen bg-gray-900">
        <Navbar class="w-full">

            <NavBody class="max-w-2xl">

                <NavbarLogo />

                <NavItems :items="navItems" />

                <NavbarButton as="a" href="/login" variant="dark">
                    Sign In
                </NavbarButton>

            </NavBody>

            <MobileNav class="max-w-md">

                <MobileNavHeader>
                    <NavbarLogo />

                    <MobileNavToggle />
                </MobileNavHeader>

                <MobileNavMenu>

                    <a v-for="item in navItems" :key="item.name" :href="item.link" class="px-2 py-1 text-lg font-medium text-gray-700 hover:text-blue-500">
                        {{ item.name }}
                    </a>

                    <NavbarButton as="a" href="/signup" variant="gradient" class="w-full mt-4">
                        Get Started
                    </NavbarButton>
                </MobileNavMenu>

            </MobileNav>

        </Navbar>
    </div>
</template>
