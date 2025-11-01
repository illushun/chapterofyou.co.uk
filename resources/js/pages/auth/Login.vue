<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

import NavBar from '@/components/NavBar.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <NavBar />

    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
        class="!p-0"
    >
        <Head title="Log in" />

        <div
            class="
                w-full max-w-sm mx-auto
                rounded-xl border-2 border-copy bg-[var(--primary-content)] p-0
                shadow-xl transition-all
            "
        >
            <div
                class="
                    relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-8 md:p-10
                    flex flex-col gap-6
                "
            >
                <div
                    v-if="status"
                    class="mb-4 text-center text-sm font-medium text-green-600"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="AuthenticatedSessionController.store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-6 w-full"
                >
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="email" class="text-copy font-semibold">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                class="border-2 border-copy rounded-lg p-3 focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-copy font-semibold">Password</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-sm text-secondary hover:text-secondary-dark transition"
                                    :tabindex="5"
                                >
                                    Forgot password?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Password"
                                class="border-2 border-copy rounded-lg p-3 focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3 text-copy-light cursor-pointer">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    :tabindex="3"
                                    class="border-copy data-[state=checked]:bg-[var(--primary)] data-[state=checked]:text-primary-content"
                                />
                                <span>Remember me</span>
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 w-full border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 hover:bg-primary-dark"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                            style="background-color: var(--primary); color: var(--primary-content);"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-4 w-4 animate-spin mr-2"
                            />
                            Log in
                        </Button>

                        <a
                            :href="route('socialite.redirect', 'google')"
                            class="w-full inline-flex items-center justify-center p-3 text-lg font-bold rounded-lg bg-white text-copy border-2 border-copy transition-colors duration-300 hover:bg-gray-50"
                            data-test="google-login-button"
                        >
                            <svg class="w-6 h-6 mr-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" width="64" height="64"><defs><path id="A" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></defs><clipPath id="B"><use xlink:href="#A"/></clipPath><g transform="matrix(.727273 0 0 .727273 -.954545 -1.45455)"><path d="M0 37V11l17 13z" clip-path="url(#B)" fill="#fbbc05"/><path d="M0 11l17 13 7-6.1L48 14V0H0z" clip-path="url(#B)" fill="#ea4335"/><path d="M0 37l30-23 7.9 1L48 0v48H0z" clip-path="url(#B)" fill="#34a853"/><path d="M48 48L17 24l-4-3 35-10z" clip-path="url(#B)" fill="#4285f4"/></g></svg>
                            Login with Google
                        </a>
                    </div>

                    <div class="text-center text-sm text-copy">
                        Don't have an account?
                        <TextLink :href="register()" :tabindex="5" class="text-secondary-content hover:text-secondary-dark transition font-semibold">Sign up</TextLink>
                    </div>
                </Form>
            </div>
        </div>

    </AuthBase>
</template>
