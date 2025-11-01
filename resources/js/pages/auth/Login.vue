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
                            class="w-full inline-flex items-center justify-center p-3 text-lg font-bold rounded-lg bg-white text-copy border-2 border-copy shadow-lg transition-colors duration-300 hover:bg-gray-50"
                            data-test="google-login-button"
                        >
                            <svg
                                class="w-6 h-6 mr-3"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M12 4.41c2.44 0 4.07.98 4.96 1.83l3.2-3.19C18.06 1.82 15.25.99 12 .99 7.08.99 2.95 3.19.96 7.07l3.85 3c.96-2.82 4.04-4.66 7.19-4.66z"
                                    fill="#ea4335"
                                />
                                <path
                                    d="M23.36 12c0-.85-.08-1.5-.18-2.18H12v4.11h6.35c-.27 1.34-1.05 2.5-2.22 3.28l3.35 2.6c2.08-1.93 3.38-4.78 3.38-8.01z"
                                    fill="#4285f4"
                                />
                                <path
                                    d="M4.81 14.99c-.43-1.3-.67-2.7-.67-4.14s.24-2.84.67-4.14L.96 7.07C.33 8.35 0 9.77 0 11.23c0 1.46.33 2.88.96 4.16l3.85-2.4z"
                                    fill="#fbbc05"
                                />
                                <path
                                    d="M12 20.91c3.15 0 5.86-1.04 7.82-2.92l-3.35-2.6c-.84.55-1.92.89-3.47.89-3.15 0-5.83-1.84-7.18-4.66l-3.85 3c2.02 3.88 6.16 6.07 10.03 6.07z"
                                    fill="#34a853"
                                />
                            </svg>
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
